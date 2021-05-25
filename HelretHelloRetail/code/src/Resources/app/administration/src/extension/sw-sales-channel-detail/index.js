const { Component, Mixin } = Shopware;

Component.override('sw-sales-channel-detail', {

    inject: [
        'helloRetailService',
    ],

    mixins: [
        Mixin.getByName('notification')
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
        saveFinish() {

            /* stop if not is Hello Retail channel */
            if (this.helloRetailService.getTypeId() !== this.salesChannel.typeId) {
                return;
            }
            /* Get feeds */
            let feeds = JSON.parse(JSON.stringify(this.salesChannel.configuration.feeds));
            /* Generate feeds based on objects keys eg order and product */
            Object.keys(feeds).forEach(feed => {
                this.helloRetailService.generateFeed(this.salesChannel.id, feed)
                    .then((response) => {
                        if (response.error) {
                            this.createNotificationError({
                                title: this.$t('Error'),
                                message: response.message
                            });
                        } else {
                            this.createNotificationSuccess({
                                title: this.$t('Success'),
                                message: response.message
                            })
                        }
                    });
            })
        }
    }
});
