import Plugin from 'src/plugin-system/plugin.class';

export default class CartTrackerPlugin extends Plugin {
    static options = {
        total: 0,
        productNumbers: [],
        email: null,
    }

    init() {
        this.interval = setInterval(() => {
            if (this._getCartApi()) {
                clearInterval(this.interval);

                if (!this.options.productNumbers || !this.options.productNumbers.length) {
                    this._clearCart();
                } else {
                    this._setCart(this.options);
                }
            }
        }, 100);
    }

    _setCart(cart) {
        if (!cart.email) {
            delete cart.email;
        }

        this._getCartApi().setCart(cart);
    }

    _clearCart() {
        this._getCartApi().clearCart();
    }

    _getCartApi() {
        if (window.ADDWISH_PARTNER_NS && window.ADDWISH_PARTNER_NS.api && window.ADDWISH_PARTNER_NS.api.cart) {
            return window.ADDWISH_PARTNER_NS.api.cart;
        }

        return null;
    }
}
