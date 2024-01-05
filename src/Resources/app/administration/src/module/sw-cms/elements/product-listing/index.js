import './config';

const cmsService = Shopware.Service('cmsService');

const config = cmsService.getCmsElementConfigByName('product-listing');

config.defaultConfig = {
    ...config.defaultConfig,
    helloRetailKey: {
        source: 'static',
        value: ''
    },
}
