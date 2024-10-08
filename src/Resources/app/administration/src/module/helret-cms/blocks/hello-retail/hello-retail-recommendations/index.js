import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'hello-retail-recommendations',
    label: 'sw-cms.blocks.hello-retail.hello-retail-recommendations.label',
    category: 'hello-retail',
    component: 'sw-cms-block-hello-retail-recommendations',
    previewComponent: 'sw-cms-preview-hello-retail-recommendations',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '0px',
        marginRight: '0px',
        sizingMode: 'boxed'
    },
    slots: {
        hrRecommendations: 'hello-retail-recommendations',
    }
});
