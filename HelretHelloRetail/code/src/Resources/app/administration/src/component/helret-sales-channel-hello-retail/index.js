import template from './helret-sales-channel-hello-retail.html.twig';

const {Component, Mixin, Utils} = Shopware;
const {Criteria} = Shopware.Data;
const {mapApiErrors} = Component.getComponentHelper();

Component.register('helret-sales-channel-hello-retail', {
    template,

    inject: [
        'salesChannelService',
        'repositoryFactory',
        'helloRetailTemplateService',
        'helloRetailService'
    ],

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification')
    ],

    props: {
        salesChannel: {
            type: Object,
            required: true
        },

        isLoading: {
            type: Boolean,
            default: false
        },
    },

    data() {
        return {
            loading: true,
            storefrontSalesChannelId: null,
            feedValues: [],
            feedGeneratingList: [],
            originalFeedValues: null
        }
    },

    created() {
        this.initChannel();
    },

    mounted() {
        Promise.all([
            this.fillSalesChannel()
        ]).then(() => {
            this.loading = false
        });
    },

    computed: {
        ...mapApiErrors('salesChannel', ['name']),

        storefrontSalesChannelCriteria() {
            const criteria = new Criteria();

            return criteria.addFilter(Criteria.equals('typeId', '8a243080f92e4c719546314b577cf82b'));
        },

        storefrontSalesChannelDomainCriteria() {
            const criteria = new Criteria();

            return criteria.addFilter(Criteria.equals('salesChannelId', this.salesChannel.configuration.storefrontSalesChannelId || null));
        },

        globalDomainRepository() {
            return this.repositoryFactory.create('sales_channel_domain');
        },

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        feedsList() {
            return Object.keys(this.salesChannel.configuration.feeds).sort((a, b) => {
                return a.localeCompare(b);
            })
        },
    },

    methods: {
        initChannel() {
            if (!this.salesChannel.configuration) {
                this.$set(this.salesChannel, 'configuration', {
                    feeds: this.helloRetailTemplateService.getExportTemplateRegistry(),
                    feedDirectory: Utils.createId()
                });
            }

            if (!this.salesChannel.configuration.feeds) {
                this.$set(this.salesChannel.configuration, 'feeds', this.helloRetailTemplateService.getExportTemplateRegistry());
            }

            if (!this.salesChannel.configuration.feedDirectory) {
                this.$set(this.salesChannel.configuration, 'feedDirectory', Utils.createId());
            }

            this.originalFeedValues = JSON.parse(JSON.stringify(this.salesChannel.configuration.feeds));
        },

        fillSalesChannel() {
            if (!this.salesChannel.configuration.storefrontSalesChannelId) {
                return this.salesChannelRepository.search(this.storefrontSalesChannelCriteria, Shopware.Context.api)
                    .then((storefrontChannels) => {
                        if (storefrontChannels.total === 1) {
                            const id = storefrontChannels.first().id;
                            this.$set(this.salesChannel.configuration, 'storefrontSalesChannelId', id);
                            this.$refs.storefrontSalesChannelId.$emit('change', id);
                        }
                        return Promise.resolve();
                    })
            }
        },

        onStorefrontSelectionChange(storefrontSalesChannelId) {
            if (!storefrontSalesChannelId) return;

            this.salesChannelRepository.get(storefrontSalesChannelId, Shopware.Context.api).then(
                (entity) => {
                    this.salesChannel.languageId = entity.languageId;
                    this.salesChannel.languages = entity.languages;
                    this.salesChannel.currencyId = entity.currencyId;
                    this.salesChannel.paymentMethodId = entity.paymentMethodId;
                    this.salesChannel.shippingMethodId = entity.shippingMethodId;
                    this.salesChannel.countryId = entity.countryId;
                    this.salesChannel.navigationCategoryId = entity.navigationCategoryId;
                    this.salesChannel.navigationCategoryVersionId = entity.navigationCategoryVersionId;
                    this.salesChannel.customerGroupId = entity.customerGroupId;

                    if (this.salesChannel.configuration.storefrontSalesChannelId) {
                        this.globalDomainRepository.search(this.storefrontSalesChannelDomainCriteria, Shopware.Context.api)
                            .then((domains) => {
                                if (domains.total === 1) {
                                    this.$set(this.salesChannel.configuration, 'salesChannelDomainId', domains.first().id)
                                }
                            })
                    }

                    if (!this.salesChannel.accessKey) {
                        this.generateKey();
                    }

                    this.forceUpdate();
                });
        },

        generateKey() {
            this.salesChannelService.generateKey().then(
                (response) => {
                    this.salesChannel.accessKey = response.accessKey;
                }
            ).catch(
                () => {
                    this.createNotificationError(
                        {
                            title: this.$tc('sw-sales-channel.detail.titleAPIError'),
                            message: this.$tc('sw-sales-channel.detail.messageAPIError')
                        }
                    );
                }
            );
        },

        forceUpdate() {
            this.$forceUpdate();
        },

        enableGenerateFeed(feed) {
            const originalFile = this.originalFeedValues[feed].file;

            return originalFile !== null && originalFile === this.salesChannel.configuration.feeds[feed].file;
        },

        setFeedGenerating(feed) {
            if (this.isFeedGenerating(feed)) {
                return;
            }

            this.feedGeneratingList.push(feed);
        },

        isFeedGenerating(feed) {
            return this.feedGeneratingList.indexOf(feed) > -1;
        },

        removeFeedGenerating(feed) {
            this.feedGeneratingList.splice(this.feedGeneratingList.indexOf(feed), 1);
        },

        generateFeed(feed) {
            this.setFeedGenerating(feed);
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
                }).finally(() => this.removeFeedGenerating(feed));
        }
    }
});
