#! /bin/bash
# Accept a bare hostname, but tolerate a pasted scheme or trailing slash. HR_CORE_HOST and HR_DASHBOARD_HOST
# also reach docker-compose's extra_hosts, which rejects a scheme noisily; HR_CDN_HOST has no such guard, and
# an unstripped one would silently produce cdn_host=https://https://... and fail every asset in the browser.
hr_host() {
    local value="${1#http://}"
    value="${value#https://}"
    printf '%s' "${value%/}"
}

# The local Hello Retail app's hosts. Override them if you run the app somewhere else; the defaults are
# the primary local instance from docker-dev-env.
# Apply a change with `docker compose up -d`, which RECREATES the container. `docker compose restart` does
# not: it stops and starts the container that already exists, and a container's environment is fixed when it
# is created, so the new value never arrives and this script reports `already current` while the storefront
# keeps serving the old host.
HR_CORE_HOST="$(hr_host "${HR_CORE_HOST:-core.dev.helloretail.com}")"
HR_DASHBOARD_HOST="$(hr_host "${HR_DASHBOARD_HOST:-my.dev.helloretail.com}")"
# The SDK's asset CDN. Deliberately has NO default: leave it unset and the SDK keeps loading assets from
# production, which works. The dev-env has no nginx server block for helloretailcdn.test (nor for the old
# d1pna5l3xsntoj.cloudfront.test), so both fall through to the default 443 block, get proxied to
# host.docker.internal:8080 and answer 502 -- dead when the developer runs on 8081-8084, and the wrong
# instance even when 8080 is up. cdn_host feeds slick/swiper, ppBusiness.js, feed-loader.gif and the partner
# stylesheets, so defaulting it to a .test name turns working asset loads into 502s. Set it only when you
# are actually serving the CDN locally.
HR_CDN_HOST="$(hr_host "${HR_CDN_HOST:-}")"

# Everything this script reports goes to stderr, progress included. Docker captures stdout and stderr as two
# separate streams and `docker logs` merges them by arrival, so a mixed script prints out of order: the
# warnings and errors below would appear ABOVE the per-file lines they summarise. One stream, one order.
echo "Starting entrypoint" >&2

# The deployed plugin, not the repo's ./src: docker-compose mounts the `shopware` named volume over
# /usr/app/src and does not bind-mount the plugin source, so this is the copy the storefront renders.
# Patching it therefore cannot reach the developer's git checkout.
PLUGIN_DIR=/usr/app/src/custom/plugins/HelloRetail

# The hosts are not a plugin setting. config.xml declares no field for them, and the storefront's
# Resources/views/storefront/layout/meta.html.twig calls `hrq.push(['init'])` with no configuration object
# at all, so the SDK falls back to its built-in production hosts. Point it at the local app by writing the
# override object into that init call.
#
# Key spelling is the SDK's, not the old fragment's: helloretail.js merges the object straight onto its conf
# (`Object.assign(_.conf, conf)`), and conf only ever reads cdn_host / core_host / dashboard_host. The old
# sed's `server_host` is dropped -- it is in neither SDK, and the legacy loader's fragment parser has an
# explicit key allowlist that never contained it, so it was always discarded.
echo "Pointing the storefront SDK at the local app" >&2

HR_CDN_HOST="$HR_CDN_HOST" HR_CORE_HOST="$HR_CORE_HOST" HR_DASHBOARD_HOST="$HR_DASHBOARD_HOST" \
PLUGIN_DIR="$PLUGIN_DIR" php -r '
$hosts = array(
    "core_host" => "https://" . getenv("HR_CORE_HOST"),
    "dashboard_host" => "https://" . getenv("HR_DASHBOARD_HOST"),
);
$cdn = getenv("HR_CDN_HOST");
if ($cdn !== false && $cdn !== "") {
    $hosts["cdn_host"] = "https://" . $cdn;
}

// cdn_host is managed either way: written when HR_CDN_HOST is set, and removed when it is not. Only adding
// it would let a value an earlier start wrote outlive the variable and keep 502ing forever on a volume that
// survives -- the same freeze that left the old sed pinning core.helloretail.test.
$apply = function (array $current) use ($hosts) {
    $merged = array_merge($current, $hosts);
    if (!isset($hosts["cdn_host"])) {
        unset($merged["cdn_host"]);
    }
    return $merged;
};
$dir = getenv("PLUGIN_DIR");

// present:    templates that exist in the deployed plugin.
// patched:    templates that now carry our hosts (freshly written, or already correct).
// unmatched:  templates that exist but whose init call we did not recognise.
// left_alone: templates holding an object we refuse to guess at.
// Counting these is the point: a rewrite that quietly matches nothing is precisely the failure this script
// exists to end, so "no error" must never be mistaken for "the hosts were applied".
//
// Know what patched does NOT prove: it counts a file on disk carrying our hosts, never that the storefront
// RENDERS that file. Reachability is not checked here, and could not be without rendering a page.
// On the current image the two candidates are not equal in that respect. The template that actually reaches
// the browser is component/hello-retail-tracking.html.twig, and it gets there through the plugin copy of
// storefront/base.html.twig, which sw_includes it inside the storefront block `base_script_csrf`. The
// plugin layout/meta.html.twig sw_includes the very same component from `layout_head_javascript_csrf` -- a
// block the vendor meta.html.twig nests inside a feature(FEATURE_NEXT_15917) condition that is off -- so
// that override is inert: it compiles, it is patched, and it never renders. Confirmed live with injected
// markers: a marker in meta.html.twig does not appear in the page, one in base.html.twig does.
// The trap that follows: a future plugin version that carries the init call ONLY in meta.html.twig, in a
// block that likewise does not render, would be written, scored patched = 1, and exit 0 here while the
// storefront still talks to the PRODUCTION Hello Retail hosts. This gate cannot catch that.
$state = array("present" => 0, "patched" => 0, "unmatched" => 0, "left_alone" => 0);

// $build returns the replacement text, or null to mean "do not touch this one".
$rewrite = function ($file, $pattern, $build) use (&$state) {
    $name = basename($file);
    if (!is_file($file)) {
        fwrite(STDERR, "entrypoint: " . $name . " is not deployed, skipping\n");
        return;
    }
    $state["present"]++;

    $before = file_get_contents($file);
    if ($before === false) {
        fwrite(STDERR, "entrypoint: ERROR - cannot read " . $name . "\n");
        exit(1);
    }

    $left_alone = false;
    $after = preg_replace_callback(
        $pattern,
        function ($m) use ($build, &$left_alone) {
            $replacement = $build($m);
            if ($replacement === null) {
                $left_alone = true;
                return $m[0];
            }
            return $replacement;
        },
        $before,
        -1,
        $count
    );

    if ($after === null) {
        fwrite(STDERR, "entrypoint: ERROR - pattern failed on " . $name . ": " . preg_last_error_msg() . "\n");
        exit(1);
    }
    if ($count === 0) {
        // Deployed, but the init call does not look the way we expect: a refactor, an added Twig filter, or
        // the call having moved into the compiled storefront bundle. Report it rather than passing silently.
        $state["unmatched"]++;
        fwrite(STDERR, "entrypoint: " . $name . " carries no init call we recognise\n");
        return;
    }
    if ($left_alone) {
        // Deliberately NOT counted as patched: the hosts are whatever that object already says.
        $state["left_alone"]++;
        fwrite(STDERR, "entrypoint: left the hand-written init object in " . $name . " alone\n");
        return;
    }
    if ($after !== $before) {
        // An unchecked write would print "updated" even on a read-only or wrong-owner file, which is the
        // same silent-success trap as a non-matching pattern.
        if (file_put_contents($file, $after) === false) {
            fwrite(STDERR, "entrypoint: ERROR - cannot write " . $name . "\n");
            exit(1);
        }
        fwrite(STDERR, "entrypoint: updated " . $name . "\n");
    } else {
        fwrite(STDERR, "entrypoint: already current " . $name . "\n");
    }
    $state["patched"]++;
};

// The template the plugin ships today. The optional `, {...}` is an object an earlier start wrote, or one
// the developer added by hand; matching it as well as the bare form is what makes this idempotent. The
// object never contains a `]`, so stopping at the first one is enough to find the end of the argument.
$rewrite(
    $dir . "/src/Resources/views/storefront/layout/meta.html.twig",
    "/hrq\.push\(\s*\[\s*([\x27\"])init\\1\s*(,[^\]]*)?\]\s*\)/",
    function ($m) use ($apply) {
        $current = array();
        if (isset($m[2])) {
            $argument = trim(substr($m[2], 1));
            $decoded = json_decode($argument, true);
            if (is_array($decoded)) {
                $current = $decoded;
            } elseif ($argument !== "") {
                // Something we did not write and cannot parse -- a Twig expression, or a variable reference.
                // Merging would mean guessing, so leave it and let the caller report it as unresolved.
                return null;
            }
        }
        // Merge, so a websiteUuid / trackingOptOut the developer put there survives and only the hosts move.
        return "hrq.push([\x27init\x27, " . json_encode($apply($current), JSON_UNESCAPED_SLASHES) . "])";
    }
);

// A volume from before the plugin moved to helloretail.js still serves the legacy loader, whose hosts ride
// in the script src fragment as a comma-separated key=value list. The image bakes an already-patched copy of
// it -- patched with the hosts of whichever start captured the image -- which is why the old sed, written to
// match only the pristine line, never fired again and left those hosts frozen. Match the patched form too.
$rewrite(
    $dir . "/src/Resources/views/storefront/component/hello-retail-tracking.html.twig",
    "/(awAddGift\.js#\{\{\s*addWishPartnerId\s*\}\})([^\x27\"]*)/",
    function ($m) use ($apply) {
        $pairs = array();
        foreach (explode(",", $m[2]) as $pair) {
            if (strpos($pair, "=") === false) {
                continue;
            }
            list($k, $v) = explode("=", $pair, 2);
            // server_host is litter from the old sed: the fragment parser in the legacy loader matches
            // against a fixed key allowlist that never contained it, so it has never done anything.
            // Drop it rather than carry it forward.
            if (trim($k) !== "" && trim($k) !== "server_host") {
                $pairs[trim($k)] = $v;
            }
        }
        $fragment = "";
        foreach ($apply($pairs) as $k => $v) {
            $fragment .= "," . $k . "=" . $v;
        }
        return $m[1] . $fragment;
    }
);

if ($state["present"] === 0) {
    // No Hello Retail templates at all, so there is no integration to misdirect. Worth saying, not failing.
    fwrite(STDERR, "entrypoint: WARNING - the HelloRetail plugin is not deployed; nothing to point at\n");
    exit(0);
}

if ($state["patched"] === 0) {
    // The integration IS deployed but none of it carries our hosts, so the storefront would talk to the
    // production Hello Retail. Refuse to start rather than let that happen unnoticed -- silently serving
    // production from a dev box is the exact bug this script was rewritten to end.
    fwrite(STDERR, "entrypoint: ERROR - the HelloRetail plugin is deployed but no template now carries the\n");
    fwrite(STDERR, "entrypoint:   local hosts (" . $state["unmatched"] . " unrecognised, "
        . $state["left_alone"] . " left alone). The storefront would use the PRODUCTION Hello Retail hosts.\n");
    exit(1);
}

if ($state["left_alone"] > 0) {
    fwrite(STDERR, "entrypoint: WARNING - " . $state["left_alone"] . " template(s) hold a hand-written init\n");
    fwrite(STDERR, "entrypoint:   object that was left untouched; their hosts are whatever that object says.\n");
}
' || exit 1

# Twig compiles templates to PHP under var/cache/<env>_<hash>/twig, and the image ships a warm cache built
# from the templates as they were when it was captured. Under APP_ENV=dev Twig auto-reloads on mtime, so this
# is redundant there, but the image's own .env sets APP_ENV=prod, where it is required. It is cheap either way.
echo "Clearing the compiled twig templates" >&2
rm -rf /usr/app/src/var/cache/*/twig

# The same .env sets SHOPWARE_HTTP_CACHE_ENABLED=1 with SHOPWARE_HTTP_DEFAULT_TTL=7200, and that flag is read
# straight from the environment (shopware.http.cache.enabled), NOT gated on APP_ENV -- so whole anonymous page
# responses are cached with the old hosts baked into the body. Without this, a developer who repoints
# HR_CORE_HOST, runs `docker compose up -d` and opens the homepage anonymously keeps seeing the previous host
# for up to two hours; only logging in or filling the cart bypasses it.
# cache.http is backed by the cache.app filesystem pool (framework.yaml: cache.http -> adapter: cache.app), so
# it lives in pools/app. Dropping that directory also drops cache.object/tags/rate_limiter, which rebuild on
# demand; pools/system (container metadata) is kept, and the theme is NOT recompiled -- theme:compile only
# builds SCSS and JS, which a template edit cannot affect, and it is far too slow to run on every start.
# `bin/console cache:pool:clear cache.http` would be surgical, but it boots the kernel and needs the database,
# and compose declares no depends_on for shopwaredb, so it cannot be relied on at start.
echo "Clearing the cached HTTP responses" >&2
rm -rf /usr/app/src/var/cache/*/pools/app

exec $@
