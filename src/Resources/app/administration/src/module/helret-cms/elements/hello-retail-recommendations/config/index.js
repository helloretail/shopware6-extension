import template from './sw-cms-el-config-hello-retail-recommendations.html.twig';

const { Component } = Shopware;

export default {
    template,

    methods: {
        createdComponent() {
            this.initElementConfig('hello-retail-recommendations');
        },
    }
}
