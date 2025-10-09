Shopware.Component.register('sw-cms-block-hello-retail', () => import('./component'));
Shopware.Component.register('sw-cms-preview-hello-retail', () => import('./preview'));

Shopware.Service('cmsService').registerCmsBlock({
    name: 'hello-retail',
    label: 'sw-cms.blocks.commerce.hello-retail.label',
    category: 'hello-retail',
    component: 'sw-cms-block-hello-retail',
    previewComponent: 'sw-cms-preview-hello-retail',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: 'Enter Recommendation ID Here'
                    }
                }
            }
        }
    }
});
