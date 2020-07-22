import HelloRetailService from './service/api/hello.retail.api.service';
import './service/hello-retail-templates.service';

import './app/init/svg-icons.init';
import './app/export-templates';

import './extension/sw-sales-channel-detail-base';
import './extension/sw-sales-channel-create-base';

import './component/wexo-sales-channel-hello-retail';

import enGb from '../../../translations/en_GB/messages.en-GB.json';

const { Application } = Shopware;

Shopware.Locale.extend('en-GB', enGb);
Shopware.Locale.extend('de-DE', enGb);
Shopware.Locale.extend('da-DK', enGb);

Application.addServiceProvider('helloRetailService', () => {
    const serviceContainer = Application.getContainer('service');
    const initContainer = Application.getContainer('init');

    return new HelloRetailService(initContainer.httpClient, serviceContainer.loginService);
});
