
Shopware.Component.override('sw-sales-channel-detail', () =>import ('./page/sw-sales-channel-detail'));

Shopware.Component.override('sw-sales-channel-detail-base', () => import('./view/sw-sales-channel-detail-base'));
Shopware.Component.register('sw-sales-channel-detail-hello-retail-comparison', () => import('./view/hello-retail-comparison'));


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
