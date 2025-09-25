const Plugin = window.PluginBaseClass;



export default class OffCanvasCartRecommendationsPlugin extends Plugin {
    static options = {
        recommendationsUrl: '/hello-retail/cart/recommendations',
        cartSelector: '.cart-offcanvas .offcanvas-body',
        recommendationsSelector: '.offcanvas-cart-recommendations',
        hrRecom: '.hr-recom'
    }

    init() {
        this._client = new HttpClient();
        this._registerEvents();
    }

    _registerEvents() {
        var pluginInstances = window.PluginManager.getPluginInstances('OffCanvasCart');

        pluginInstances[0].$emitter.subscribe('offCanvasOpened');

        this._onOffcanvasOpened()
    }

    _onOffcanvasOpened() {
        this.fetch();
    }

    fetch() {
        fetch(this.options.recommendationsUrl)
    .then(response => response.text())
    .then((response) => {
        const offcanvasCart = DomAccess.querySelector(document, this.options.cartSelector);
            const recommendationsContainer = offcanvasCart.querySelector(this.options.recommendationsSelector);

            if (recommendationsContainer) {
                recommendationsContainer.innerHTML = response;
                DomAccess.querySelector(document, this.options.hrRecom).classList.remove('d-none');
            }
    });
    }
}