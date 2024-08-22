import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import DomAccess from 'src/helper/dom-access.helper';

export default class OffCanvasCartRecommendationsPlugin extends Plugin {
    static options = {
        recommendationsUrl: '/checkout/cart/recommendations',
        cartSelector: '.cart-offcanvas .offcanvas-body',
        recommendationsSelector: '.offcanvas-cart-recommendations',
    }
    init() {
        this._client = new HttpClient();
        console.log('init')
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
        this._client.get(this.options.recommendationsUrl, (response) => {
            const offcanvasCart = DomAccess.querySelector(document, this.options.cartSelector);
            const recommendationsContainer = offcanvasCart.querySelector(this.options.recommendationsSelector);

            if (recommendationsContainer) {
                recommendationsContainer.innerHTML = response;
                document.querySelector('.hr-recom').classList.remove('d-none');
            }
        }, (error) => {
            console.error('Error fetching recommendations:', error);
        });
    }
}