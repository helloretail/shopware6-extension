import template from './sw-sales-channel-detail-base.html.twig';

const {Component} = Shopware;

Component.override('sw-sales-channel-detail-base', {
    template,

    inject: ['helloRetailService'],

    computed: {
        isHelloRetail() {
            return this.salesChannel && this.salesChannel.typeId === this.helloRetailService.getTypeId();
        }
    },

    methods: {
        createCategoryCollections() {
            // Ignore category requests, slightly faster admin. Saves 3x category requests
            // Categories aren't needed (HR Admin)
        },
    }
});
