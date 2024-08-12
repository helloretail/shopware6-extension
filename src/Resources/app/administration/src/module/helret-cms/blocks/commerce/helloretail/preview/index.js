import template from './sw-cms-preview-hello-retail.html.twig';

export default {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
}