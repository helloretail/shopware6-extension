import template from './sw-cms-block-hello-retail.html.twig';
import './sw-cms-block-hello-retail.scss';

export default {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
}