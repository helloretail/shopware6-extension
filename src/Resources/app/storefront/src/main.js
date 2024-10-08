import AddToCartCSRFPlugin from "./plugins/add-to-cart-csrf.plugin";
import CartTrackerPlugin from "./plugins/cart-tracker.plugin";
// import ProductListingPlugin from "./plugins/product-listing.plugin";

const PluginManager = window.PluginManager;

PluginManager.register('AddToCartCSRF', AddToCartCSRFPlugin)
PluginManager.register('HelretCartTracker', CartTrackerPlugin, '[data-helret-cart-tracker]');
// PluginManager.register('ProductListingPlugin', ProductListingPlugin);