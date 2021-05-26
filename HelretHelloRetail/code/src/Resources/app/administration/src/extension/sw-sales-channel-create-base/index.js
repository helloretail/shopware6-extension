import template from '../sw-sales-channel-detail-base/sw-sales-channel-detail-base.html.twig';

const { Component } = Shopware;

Component.override('sw-sales-channel-create-base', {
    template,

    watch: {
        typeId() {
            if (this.isHelloRetail) {
                this.salesChannel.configuration = {};
            }
        }
    },

    computed: {
        isHelloRetail() {
            return this.$route.params.typeId === this.helloRetailService.getTypeId();
        }
    },

});
