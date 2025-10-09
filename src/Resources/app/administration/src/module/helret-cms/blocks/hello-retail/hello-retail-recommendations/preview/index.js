import template from './sw-cms-preview-hello-retail-recommendations.html.twig';


export default {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    }
}
