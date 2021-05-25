import HelloRetailService from './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/init/svg-icons.init';
import './app/export-templates';

import './extension/sw-sales-channel-detail';
import './extension/sw-sales-channel-detail-base';
import './extension/sw-sales-channel-create-base';

import './component/helret-sales-channel-hello-retail';

import './module/helret-cms/blocks/commerce/helloretail';

import enGb from './snippet/en-GB.json';
import deDE from './snippet/de-DE.json';
import daDK from './snippet/da-DK.json';

const { Application, Locale } = Shopware;

Locale.extend('en-GB', enGb);
Locale.extend('de-DE', deDE);
Locale.extend('da-DK', daDK);

Application.addServiceProvider('helloRetailService', () => {
    const serviceContainer = Application.getContainer('service');
    const initContainer = Application.getContainer('init');

    return new HelloRetailService(initContainer.httpClient, serviceContainer.loginService);
});
