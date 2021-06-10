import template from './helret-sales-channel-hello-retail.html.twig';

const {Component, Mixin, Utils} = Shopware;
const {Criteria} = Shopware.Data;
const {mapPropertyErrors} = Component.getComponentHelper();

Component.register('helret-sales-channel-hello-retail', {
    template,

    inject: [
        'salesChannelService',
        'repositoryFactory',
        'helloRetailTemplateService',
        'helloRetailService',
        'acl'
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
            showDeleteModal: false, // Handle the deletion of the sales channel(s)
            loading: true,
            storefrontSalesChannelId: null,
            feedValues: [],
            originalFeedValues: null,
            storefrontDomainUrl: ""
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
        ...mapPropertyErrors('salesChannel', ['name']),

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
            this.getStorefrontDomain();
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

        getStorefrontDomain() {
            const { salesChannelDomainId } = this.salesChannel.configuration;
            if(!salesChannelDomainId){
                return;
            }

            const criteria = new Criteria;
            criteria.addFilter(Criteria.equals('id', salesChannelDomainId));

            this.globalDomainRepository.search(criteria, Shopware.Context.api).then(r =>
                r.first() ?
                    this.storefrontDomainUrl = r.first().url :
                    null
            );
        },

        feedUrl(feed){
            const _feed = this.salesChannel.configuration.feeds[feed]||false;

            if(!_feed || _feed.file === null){
                return;
            }
            const { feedDirectory, salesChannelDomainId } = this.salesChannel.configuration;
            if(!feedDirectory || !salesChannelDomainId){
                return;
            }
            const criteria = new Criteria;
            criteria.addFilter(Criteria.equals('id', salesChannelDomainId));
            let urlPath = `/hello-retail/${feedDirectory}/${_feed.file}`;
            return this.storefrontDomainUrl + urlPath;
        },

        // Handle the deletion of the sales channel(s)
        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete() {
            this.showDeleteModal = false;

            this.$nextTick(() => {
                this.deleteSalesChannel(this.salesChannel.id);
                this.$router.push({ name: 'sw.dashboard.index' });
            });
        },
        deleteSalesChannel(salesChannelId) {
            this.salesChannelRepository.delete(salesChannelId, Shopware.Context.api).then(() => {
                this.$root.$emit('sales-channel-change');
            });
        },

    }
});
