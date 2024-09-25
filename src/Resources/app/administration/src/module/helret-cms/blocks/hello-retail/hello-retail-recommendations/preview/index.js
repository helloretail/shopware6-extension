import template from './sw-cms-preview-hello-retail-recommendations.html.twig';

const { Component } = Shopware;

Component.register('sw-cms-preview-hello-retail-recommendations', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    }
});
