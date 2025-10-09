import template from './sw-cms-el-hello-retail-recommendations.html.twig';
import './sw-cms-el-hello-retail-recommendations.scss';


export default {
    template,
    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('hello-retail-recommendations');
            this.initElementData('hello-retail-recommendations');
        }
    }
}
