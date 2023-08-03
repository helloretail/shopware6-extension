#! /bin/bash
echo starting entrypoint
sed -i "s/'https:\/\/d1pna5l3xsntoj.cloudfront.net\/scripts\/company\/awAddGift.js#{{ addWishPartnerId }}';/'https:\/\/d1pna5l3xsntoj.cloudfront.net\/scripts\/company\/awAddGift.js#{{ addWishPartnerId }},server_host=https:\/\/addwish.test,cdn_host=https:\/\/d1pna5l3xsntoj.cloudfront.test,core_host=https:\/\/core.helloretail.test,dashboard_host=https:\/\/my.helloretail.test';/g" /usr/app/src/custom/plugins/HelloRetail/src/Resources/views/storefront/component/hello-retail-tracking.html.twig
exec $@