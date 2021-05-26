const { Component, Mixin } = Shopware;
import saveFinish from "../utils/saveFinish"
const utils = Shopware.Utils;

const insertIdIntoRoute = (to, from, next) => {
    if (to.name.includes('sw.sales.channel.create') && !to.params.id) {
        to.params.id = utils.createId();
    }

    next();
};


Component.override('sw-sales-channel-create', {

    inject: [
        'helloRetailService',
        'salesChannelService',
    ],

    mixins: [
        Mixin.getByName('notification')
    ],
    computed: {
        allowSaving() {
            return this.acl.can('sales_channel.creator');
        }
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');
        },

        saveFinish,

        onSave() {
            this.$super("onSave");
        }
    }
});
