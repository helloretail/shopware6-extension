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
            storefrontDomainUrl: "",
            exportFeeds: null,
            isEntitiesLoading: false,
            feeds: []
        }
    },

    created() {
        // Ensure salesChannel.configuration isset.
        this.initChannel();

        this.$emit("invalid-file-name", true);
        this.loading = true;

        // Await, entities and salesChannel to be loaded
        Promise.all([
            this.getStorefrontDomain(),
            this.loadFeedEntities()
        ]).finally(() => {
            this.$emit("valid-file-name", true);
            this.loading = false;
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
            return Object.keys(this.feeds).sort((a, b) => a.localeCompare(b));
        },
    },

    methods: {
        initChannel() {
            if (!this.salesChannel.configuration) {
                this.salesChannel.configuration = {};
            }

            if (!this.salesChannel.configuration.feedDirectory) {
                this.salesChannel.configuration.feedDirectory = Utils.createId()
            }

            if (!this.salesChannel.configuration.storefrontSalesChannelId) {
                this.salesChannel.configuration.storefrontSalesChannelId = null;
            }
        },

        setFeeds() {
            let feeds = {};
            if (this.exportFeeds && Object.keys(this.exportFeeds).length) {
                for (const key in this.exportFeeds) {
                    let feed = this.exportFeeds[key]
                    feeds[key] = {
                        file: feed.file,
                        name: feed.feed,
                        headerTemplate: null,
                        bodyTemplate: null,
                        footerTemplate: null
                    };
                }
            } else {
                feeds = this.helloRetailTemplateService.getExportTemplateRegistry();
                Object.keys(feeds).forEach(key => {
                    feeds[key].headerTemplate = null;
                    feeds[key].bodyTemplate = null;
                    feeds[key].footerTemplate = null;
                });
            }
            this.feeds = feeds;

            if (!this.salesChannel.configuration.feeds) {
                this.$set(this.salesChannel.configuration, 'feeds', feeds);
            }

            this.originalFeedValues = JSON.parse(JSON.stringify(this.salesChannel.configuration.feeds));
        },

        loadFeedEntities() {
            this.isEntitiesLoading = true;
            return this.helloRetailService.getExportEntities()
                .then(result => this.exportFeeds = result.feeds)
                .then(() => this.setFeeds())
                .finally(() => this.isEntitiesLoading = false);
        },

        onStorefrontSelectionChange(storefrontSalesChannelId, salesChannel) {
            if (!storefrontSalesChannelId) {
                return;
            }

            this.salesChannel.languageId = salesChannel.languageId;
            this.salesChannel.languages = salesChannel.languages;
            this.salesChannel.currencyId = salesChannel.currencyId;
            this.salesChannel.paymentMethodId = salesChannel.paymentMethodId;
            this.salesChannel.shippingMethodId = salesChannel.shippingMethodId;
            this.salesChannel.countryId = salesChannel.countryId;
            this.salesChannel.navigationCategoryId = salesChannel.navigationCategoryId;
            this.salesChannel.navigationCategoryVersionId = salesChannel.navigationCategoryVersionId;
            this.salesChannel.customerGroupId = salesChannel.customerGroupId;

            if (this.salesChannel.configuration.storefrontSalesChannelId) {
                this.globalDomainRepository.search(this.storefrontSalesChannelDomainCriteria, Shopware.Context.api)
                    .then((domains) => {
                        if (domains.total === 1) {
                            this.$set(this.salesChannel.configuration, 'salesChannelDomainId', domains.first().id)
                        }
                    });
            }

            if (!this.salesChannel.accessKey) {
                this.generateKey();
            }

            this.forceUpdate();
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
            const {salesChannelDomainId} = this.salesChannel.configuration;
            if (!salesChannelDomainId) {
                return;
            }

            const criteria = new Criteria;
            criteria.addFilter(Criteria.equals('id', salesChannelDomainId));

            return this.globalDomainRepository.search(criteria, Shopware.Context.api).then(r =>
                r.first() ?
                    this.storefrontDomainUrl = r.first().url :
                    null
            );
        },

        feedUrl(feed) {
            const _feed = this.exportFeeds[feed] || this.salesChannel.configuration.feeds[feed] || false;
            if (!_feed || _feed.file === null) {
                return;
            }
            const {feedDirectory, salesChannelDomainId} = this.salesChannel.configuration;
            if (!feedDirectory || !salesChannelDomainId) {
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
                this.$router.push({name: 'sw.dashboard.index'});
            });
        },
        deleteSalesChannel(salesChannelId) {
            this.salesChannelRepository.delete(salesChannelId, Shopware.Context.api).then(() => {
                this.$root.$emit('sales-channel-change');
            });
        },

    }
});
