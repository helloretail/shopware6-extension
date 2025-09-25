import template from './template.html.twig';
import './style.scss';

const {Component, Mixin} = Shopware;

export default {
    template,

    inject: [
        'repositoryFactory',
        'helloRetailTemplateService',
        'helloRetailService',
        'entityMappingService',
        'acl',
    ],

    mixins: [Mixin.getByName('notification')],

    props: {
        // // FIXME: add type for salesChannel property
         
        salesChannel: {
            required: true,
        },

        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            feedType: 'product',
            feed: null,
            isEntitiesLoading: false,
            entities: null,
            feedQueued: false
        };
    },

    computed: {
        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        editorConfig() {
            return {
                enableBasicAutocompletion: true,
            };
        },

        outerCompleterFunctionHeader() {
            return this.outerCompleterFunction({
                helloRetailExport: 'helloRetailExport',
            });
        },

        outerCompleterFunctionBody() {
            return this.outerCompleterFunction({
                helloRetailExport: 'helloRetailExport',
            });
        },

        outerCompleterFunctionFooter() {
            return this.outerCompleterFunction({
                helloRetailExport: 'helloRetailExport',
            });
        },

        getConfiguration() {
            if (!this.salesChannel.configuration.feeds[this.feedType]) {
                 
                this.salesChannel.configuration.feeds[this.feedType] = {
                    name: this.feedType,
                    headerTemplate: null,
                    bodyTemplate: null,
                    footerTemplate: null,
                    file: `${this.feedType}.xml`,
                    associations: []
                };
            }

            return this.salesChannel.configuration.feeds[this.feedType];
        },

        feedName() {
            if (this.entities && this.entities[this.feedType] && this.entities[this.feedType].snippetKey) {
                return this.$tc(this.entities[this.feedType].snippetKey).toLowerCase();
            }

            if (['product', 'category', 'order'].includes(this.feedType)) {
                return this.$tc((`helret-hello-retail.comparison.feed.${this.feedType}`)).toLowerCase();
            }

            // Unknown type just write btn text as: "Generate feed"
            return '';
        },

        tooltipGenerate() {
            return {
                message: !this.salesChannel || this.salesChannel.active ?
                    this.$tc('helret-hello-retail.detail.forceGenerateTooltipSave') :
                    this.$tc('helret-hello-retail.detail.forceGenerateTooltipActivate'),
                appearance: 'light',
                showOnDisabledElements: true,
                disabled: !this.forceGenerateDisabled
            };
        },

        forceGenerateDisabled() {
            if (!this.salesChannel || !this.salesChannel.active) {
                return true;
            }

            return this.salesChannelRepository.hasChanges(this.salesChannel);
        }
    },

    watch: {
        feedType() {
            this.setFeed();
        },
        entities() {
            this.setFeed();
        }
    },

    created() {
        this.loadFeedEntities()
            .then(() => this.setFeed());
    },

    methods: {
        loadFeedEntities() {
            this.isEntitiesLoading = true;
            return this.helloRetailService.getExportEntities()
                .then(result => {
                    this.entities = result.feeds;
                })
                .finally(() => {
                    this.isEntitiesLoading = false;
                });
        },

        setFeed() {
            this.feed = this.entities[this.feedType] || null;
        },

        outerCompleterFunction(mapping) {
            return function completerFunction(prefix) {
                const entityMapping = this.entityMappingService.getEntityMapping(prefix, {
                    ...mapping,
                    [this.feedType]: this.feedType,
                });
                return Object.keys(entityMapping).map(val => {
                    return {value: val};
                });
            }.bind(this);
        },

        getFeedOptions() {
            if (this.entities && Object.keys(this.entities).length) {
                return Object.keys(this.entities).map(key => {
                    return {
                        label: this.$tc(this.entities[key].snippetKey),
                        value: this.entities[key].feed
                    };
                });
            }

            // Fallback
            return [
                {
                    label: this.$tc('helret-hello-retail.comparison.feed.product'),
                    value: 'product'
                },
                {
                    label: this.$tc('helret-hello-retail.comparison.feed.category'),
                    value: 'category'
                },
                {
                    label: this.$tc('helret-hello-retail.comparison.feed.order'),
                    value: 'order'
                }
            ];
        },

        getInheritValue(feedTemplate) {
            if (!this.feed) {
                return null;
            }

            return this.feed[feedTemplate] || null;
        },

        generateFeed(feed) {
            this.feedQueued = true;

            this.helloRetailService.generateFeed(this.salesChannel.id, feed.feed)
                .then(response => {
                    if (response.error) {
                        this.createNotificationError({message: response.message});
                    } else {
                        this.createNotificationSuccess({message: response.message});
                    }
                })
                .finally(() => {
                    this.feedQueued = false;
                });
        }
    },
}
