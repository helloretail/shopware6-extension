import Plugin from 'src/plugin-system/plugin.class';

export default class ProductListingPlugin extends Plugin {
    init() {
        let pageKey = window.hrListingData.pageKey
        let hrScript = window.hrListingData.helloRetailData.products.javascript
        let productFilters = { hierarchies: window.hrListingData.hierarchies }

        window.hrq = window.hrq || [];

        window.hrq.push(function () {
            (function (_, container, data, page) {
                data.params = data.params || {};
                // set the current category page for HR script to use
                data.params.filters = JSON.stringify(productFilters);
                eval(hrScript);
            })(
                ADDWISH_PARTNER_NS,
                document.getElementById('hr-category-page'),
                window.hrListingData.helloRetailData,
                { key: pageKey }
            );
        });
    }
}
