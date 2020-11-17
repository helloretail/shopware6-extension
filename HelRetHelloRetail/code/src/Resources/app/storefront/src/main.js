import AddToCartCSRFPlugin from "./plugins/add-to-cart-csrf.plugin";

const PluginManager = window.PluginManager;

PluginManager.register('AddToCartCSRF', AddToCartCSRFPlugin)