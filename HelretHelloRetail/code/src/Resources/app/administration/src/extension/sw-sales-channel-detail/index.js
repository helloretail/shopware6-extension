const { Component } = Shopware;

Component.override('sw-sales-channel-detail', {

    inject: [
        'helloRetailService'
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

    methods: {
        createdComponent() {
            this.$super('createdComponent');
        },
    }
});
