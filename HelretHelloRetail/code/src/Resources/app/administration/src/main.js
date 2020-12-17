import HelloRetailService from './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/init/svg-icons.init';
import './app/export-templates';

import './extension/sw-sales-channel-detail-base';
import './extension/sw-sales-channel-create-base';

import './component/helret-sales-channel-hello-retail';

import './module/helret-cms/blocks/commerce/helloretail';

import enGb from '../../../translations/en_GB/messages.en-GB.json';

const { Application, Locale } = Shopware;

Locale.extend('en-GB', enGb);
Locale.extend('de-DE', enGb);
Locale.extend('da-DK', enGb);

Application.addServiceProvider('helloRetailService', () => {
    const serviceContainer = Application.getContainer('service');
    const initContainer = Application.getContainer('init');

    return new HelloRetailService(initContainer.httpClient, serviceContainer.loginService);
});
