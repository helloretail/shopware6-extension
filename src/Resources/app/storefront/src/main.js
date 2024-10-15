import AddToCartCSRFPlugin from "./plugins/add-to-cart-csrf.plugin";
import CartTrackerPlugin from "./plugins/cart-tracker.plugin";
import OffCanvasCartRecommendationsPlugin from "./plugins/offcanvas-cart-recommendations.plugin";
import ProductListingPlugin from "./plugins/product-listing.plugin";

const PluginManager = window.PluginManager;

PluginManager.register('AddToCartCSRF', AddToCartCSRFPlugin)
PluginManager.register('HelretCartTracker', CartTrackerPlugin, '[data-helret-cart-tracker]');
PluginManager.register('OffCanvasCartRecommendations', OffCanvasCartRecommendationsPlugin, '[data-offcanvas-cart-recommendations]');
PluginManager.register('ProductListingPlugin', ProductListingPlugin, '[data-helret-listing]');
