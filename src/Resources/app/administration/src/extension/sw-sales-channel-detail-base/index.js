import template from './sw-sales-channel-detail-base.html.twig';

const {Component} = Shopware;

Component.override('sw-sales-channel-detail-base', {
    template,

    inject: [
        'helloRetailService'
    ],

    computed: {
        isHelloRetail() {
            return this.salesChannel && this.salesChannel.typeId.indexOf(this.helloRetailService.getTypeId()) !== -1;
        }
    }
});
