import template from './template.html.twig';
import './style.scss';

const {Component, Mixin} = Shopware;

Component.register('sw-sales-channel-detail-hello-retail-comparison', {
    template,

    inject: [
        "helloRetailTemplateService",
        "helloRetailService",
        'entityMappingService',
        'acl',
    ],

    mixins: [Mixin.getByName('notification')],

    props: {
        // // FIXME: add type for salesChannel property
        // // eslint-disable-next-line vue/require-prop-types
        salesChannel: {
            required: true,
        },

        isLoading: {
            type: Boolean,
            default: false,
        },
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

    data() {
        return {
            feedType: "product",
            feed: null,
            isEntitiesLoading: false,
            entities: null
        };
    },

    computed: {
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
                }
            }

            return this.salesChannel.configuration.feeds[this.feedType];
        }
    },

    methods: {
        loadFeedEntities() {
            this.isEntitiesLoading = true;
            return this.helloRetailService.getExportEntities()
                .then(result => this.entities = result.feeds)
                .finally(() => this.isEntitiesLoading = false);
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
                return Object.keys(this.entities).map(function (key) {
                    return {
                        label: this.$tc(this.entities[key].snippetKey),
                        value: this.entities[key].feed
                    };
                }.bind(this));
            }

            // Fallback
            return [
                {
                    label: this.$tc('helret-hello-retail.comparison.feed.product'),
                    value: "product"
                },
                {
                    label: this.$tc('helret-hello-retail.comparison.feed.category'),
                    value: "category"
                },
                {
                    label: this.$tc('helret-hello-retail.comparison.feed.order'),
                    value: "order"
                }
            ];
        },

        getInheritValue(template) {
            if (!this.feed) {
                return null;
            }

            return this.feed[template] || null;
        }
    },
});
