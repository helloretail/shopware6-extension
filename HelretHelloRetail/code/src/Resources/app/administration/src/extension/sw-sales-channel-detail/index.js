const { Component, Mixin } = Shopware;

import saveFinish from "../utils/saveFinish"

Component.override('sw-sales-channel-detail', {

    inject: [
        'helloRetailService',
        'salesChannelService',
    ],

    mixins: [
        Mixin.getByName('notification'),

    ],

    watch: {
        isHelloRetail() {
            this.$forceUpdate();
        }
    },

    computed: {
        isHelloRetail() {
            return this.salesChannel && this.salesChannel.typeId.indexOf(this.helloRetailService.getTypeId()) !== -1;
        },
    },

    isHelloRetail: {
        type: Boolean
    },

    methods: {
        saveFinish,
    }
});
