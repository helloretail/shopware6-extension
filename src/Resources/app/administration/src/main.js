import HelloRetailService from './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/init/svg-icons.init';
import './app/export-templates';

import './extension/sw-sales-channel-detail';
import './extension/sw-sales-channel-detail-base';
import './extension/sw-sales-channel-create';
import './extension/sw-sales-channel-create-base';

import './component/helret-sales-channel-hello-retail';

import './module/helret-cms/blocks/commerce/helloretail';
import "./module/sw-sales-channel/view/hello-retail-comparison";

const {Application} = Shopware;

Application.addServiceProvider('helloRetailService', () => {
    const serviceContainer = Application.getContainer('service');
    const initContainer = Application.getContainer('init');

    return new HelloRetailService(initContainer.httpClient, serviceContainer.loginService);
});

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


if (module.hot) {
    module.hot.accept();
}
