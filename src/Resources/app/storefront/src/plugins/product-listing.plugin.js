import Plugin from 'src/plugin-system/plugin.class';

export default class HelloRetailFilterPlugin extends Plugin {

    static options = {
        pageKey: '66f2da236ecef921304efb8b',
        hierarchies: ["Catalogue #1", "Clothing"],
        selectedFilters: {},
        responseFormat: 'json'
    }


    init() {
        this.pageKey = this.options.pageKey;
        this.hierarchies = this.options.hierarchies;
        this.selectedFilters = this.options.selectedFilters;
        this.responseFormat = this.options.responseFormat;

        window.hrq = window.hrq || [];
        this.fetchProducts();
    }

    fetchProducts() {
        const apiUrl = `https://core.helloretail.com/serve/pages/${this.pageKey}`;
        const requestData = {
            url: window.location.href,
            params: {
                filters: {
                    hierarchies: this.hierarchies
                }
            },
            products: {
                start: 0,
                count: 100,
            },
            firstLoad: true,
            layout: false
        };

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData),
        })
            .then(response => response.json())
            .then(content => {
                console.log("Content dump", content);
                // this.addContent(content);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    addContent(content) {
        let container = document.getElementById('hr-category-page');
        if (!container) {
            container = document.createElement('div');
            container.id = 'hr-category-page';
            const productListingEl = document.querySelector('.cms-block-product-listing');
            if (productListingEl) {
                productListingEl.replaceWith(container);
            }
        }
        container.innerHTML = content.products.html;

        const styleEl = document.createElement('style');
        styleEl.innerHTML = content.products.style;
        document.head.appendChild(styleEl);

        if (!window.ADDWISH_PARTNER_NS) {
            console.error('ADDWISH_PARTNER_NS is not defined');
            return;
        }

        const pageKey = this.pageKey;
        hrq = window.hrq || [];
        hrq.push(() => {
            (function (_, container, data, page) {
                    eval(data.products.javascript);
            })(
                window.ADDWISH_PARTNER_NS,
                document.getElementById('hr-category-page'),
                content,
                { key: pageKey }
            );
        });
    }
}
