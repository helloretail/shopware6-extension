import './component/helret-sales-channel-hello-retail';

import './page/sw-sales-channel-detail';

import './view/sw-sales-channel-create-base';
import './view/sw-sales-channel-detail-base';

import "./view/hello-retail-comparison";

Shopware.Module.register('hello-retail-tabs', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.sales.channel.detail') {
            currentRoute.children.push({
                name: 'sw.sales.channel.detail.hello-retail-comparison',
                path: 'hello-retail-comparison',
                component: 'sw-sales-channel-detail-hello-retail-comparison',
                meta: {
                    parentPath: 'sw.sales.channel.list',
                    privilege: 'sales_channel.viewer',
                }
            });
        }
        next(currentRoute);
    }
});
