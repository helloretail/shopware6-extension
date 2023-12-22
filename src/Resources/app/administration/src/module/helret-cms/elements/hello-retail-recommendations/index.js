import './component';
import './preview';
import './config';
const config = Shopware.Service('cmsService').getCmsElementConfigByName('product-slider').defaultConfig;
console.log(config)

Shopware.Service('cmsService').registerCmsElement({
    name: 'hello-retail-recommendations',
    label: 'sw-cms.el.hello-retail.hello-retail-recommendations.label',
    component: 'sw-cms-el-hello-retail-recommendations',
    configComponent: 'sw-cms-el-config-hello-retail-recommendations',
    previewComponent: 'sw-cms-preview-hello-retail-recommendations',
    defaultConfig: {
        ...config,
        products: {
            source: 'static',
            value: [],
            required: false,
        },
        key: {
            source: 'static',
            value: '',
        },
        type: {
            source: 'static',
            value: null,
        },
        hierarchiesFilter: {
            source: 'static',
            value: null,
        }

    },
});
