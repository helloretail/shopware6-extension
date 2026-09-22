#! /bin/bash
# The local Hello Retail app's hosts. Override them if you run the app somewhere else; the defaults are
# the primary local instance from docker-dev-env.
HR_CORE_HOST="${HR_CORE_HOST:-core.dev.helloretail.com}"
HR_DASHBOARD_HOST="${HR_DASHBOARD_HOST:-my.dev.helloretail.com}"
echo starting entrypoint
sed -i "s/'https:\/\/d1pna5l3xsntoj.cloudfront.net\/scripts\/company\/awAddGift.js#{{ addWishPartnerId }}';/'https:\/\/d1pna5l3xsntoj.cloudfront.net\/scripts\/company\/awAddGift.js#{{ addWishPartnerId }},server_host=https:\/\/addwish.test,cdn_host=https:\/\/d1pna5l3xsntoj.cloudfront.test,core_host=https:\/\/${HR_CORE_HOST},dashboard_host=https:\/\/${HR_DASHBOARD_HOST}';/g" /usr/app/src/custom/plugins/HelloRetail/src/Resources/views/storefront/component/hello-retail-tracking.html.twig
exec $@