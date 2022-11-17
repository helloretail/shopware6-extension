import template from './sw-sales-channel-detail-base.html.twig';

const {Component, Utils} = Shopware;

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
            if (!this.isHelloRetail) {
                this.$super('createCategoryCollections');
            }

            // Ignore category requests, slightly faster admin. Saves 3x category requests
            // Categories aren't needed (HR Admin)
        },


        initHelloRetailConfiguration() {
            // Ensure salesChannel.configuration isset.
            if (!this.salesChannel.configuration) {
                this.salesChannel.configuration = {};
            }

            if (!this.salesChannel.configuration.feedDirectory) {
                this.salesChannel.configuration.feedDirectory = Utils.createId()
            }

            if (!this.salesChannel.configuration.storefrontSalesChannelId) {
                this.salesChannel.configuration.storefrontSalesChannelId = null;
            }

            if (!this.salesChannel.configuration.salesChannelDomainId) {
                this.salesChannel.configuration.salesChannelDomainId = null;
            }
        }
    },


    created() {
        this.initHelloRetailConfiguration();
    }
})
;
