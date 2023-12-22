import template from './sw-cms-el-config-hello-retail-recommendations.html.twig';

const { Component } = Shopware;

Component.extend('sw-cms-el-config-hello-retail-recommendations', 'sw-cms-el-config-product-slider', {
    template,

    methods: {
        createdComponent() {
            this.initElementConfig('hello-retail-recommendations');
        },
    },
    computed: {
    }
});
