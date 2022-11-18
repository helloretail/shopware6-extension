import template from './template.html.twig';

const {Component} = Shopware;

Component.override('sw-sales-channel-detail', {
    template,

    inject: [
        'helloRetailService',
        'salesChannelService',
    ],

    data() {
        return {
            forceGenerateFeedsModal: false,
            isForceGenerating: false,
            forceSaveSuccessful: false,
        };
    },

    computed: {
        isHelloRetail() {
            return this.salesChannel && this.salesChannel.typeId === this.helloRetailService.getTypeId();
        },

        tooltipForceGenerate() {
            if (!this.allowSaving) {
                return {
                    message: this.$tc('sw-privileges.tooltip.warning'),
                    disabled: this.allowSaving,
                    showOnDisabledElements: true,
                };
            }

            return {
                message: !this.forceGenerateDisabled ?
                    this.$tc('helret-hello-retail.detail.forceGenerateTooltip') :
                    this.$tc('helret-hello-retail.detail.forceGenerateTooltipSave'),
                appearance: 'light',
                showOnDisabledElements: true,
            };
        },

        forceGenerateDisabled() {
            return this.salesChannelRepository.hasChanges(this.salesChannel);
        }
    },

    methods: {
        forceGenerateFeeds() {
            this.forceGenerateFeedsModal = false;

            this.isForceGenerating = true;
            this.helloRetailService.getExportEntities()
                .then(async result => {
                    if (!Object.keys(result.feeds).length) {
                        this.createNotificationInfo({
                            message: this.$tc('helret-hello-retail.detail.forceGenerate.noFeeds'),
                        });
                        return;
                    }

                    this.createNotificationInfo({
                        message: this.$tc('helret-hello-retail.save.info', 0, {
                            feedCount: Object.keys(result.feeds).length,
                        }),
                    });

                    /* Generate feeds based on objects keys e.g. order and product */
                    const promises = Object.keys(result.feeds).map(feed => {
                        return this.helloRetailService.generateFeed(this.salesChannel.id, feed)
                            .then(response => {
                                if (response.error) {
                                    this.createNotificationError({
                                        message: response.message,
                                    });
                                } else {
                                    this.createNotificationSuccess({
                                        message: response.message,
                                    });
                                }
                            });
                    });

                    await Promise.all(promises).then(() => {
                        this.forceSaveSuccessful = true;
                    });
                })
                .finally(() => {
                    this.isForceGenerating = false;
                });
        }
    }
});
