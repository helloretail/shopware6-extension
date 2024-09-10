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
        isShopware66 () {
            let isShopware66 = window.Shopware.Feature.flags.V6_6_0_0
            return isShopware66;
        }
    }
});
