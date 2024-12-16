<?php declare(strict_types=1);

namespace Helret\HelloRetail\Controller;

use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Storefront\Checkout\Cart\Error\PaymentMethodChangedError;
use Shopware\Storefront\Checkout\Cart\Error\ShippingMethodChangedError;
use Shopware\Storefront\Page\Checkout\Offcanvas\CheckoutOffcanvasWidgetLoadedHook;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Helret\HelloRetail\Service\HelloRetailRecommendationService;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Storefront\Controller\CheckoutController;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class CartController extends StorefrontController
{
    public function __construct(
        private readonly HelloRetailRecommendationService $helloRetailService,
        private readonly SystemConfigService $systemConfigService,
        private readonly OffcanvasCartPageLoader $offcanvasCartPageLoader
    ) {
    }

    #[Route(
        path: '/hello-retail/cart/recommendations',
        name: 'hello-retail.cart.recommendations',
        defaults: [
            '_routeScope' => ['storefront'],
            'XmlHttpRequest' => 'true'
        ],
        methods: ['GET']
    )]
    public function sidebarRecommendations(SalesChannelContext $context): Response
    {
        $data = $this->getRecommendationsData($context);

        return $this->renderStorefront(
            '@HelretHelloRetail/storefront/component/checkout/recommendations.html.twig', [
                'products' => $data['recommendations']
            ]
        );
    }

    #[Route(
        path: '/checkout/offcanvas',
        name: 'frontend.cart.offcanvas',
        options: ['seo' => false],
        methods: ['GET'],
        defaults: ['XmlHttpRequest' => true]
    )]
    public function offcanvas(Request $request, SalesChannelContext $context): Response
    {
        $data = $this->getRecommendationsData($context);
        $page = $this->offcanvasCartPageLoader->load($request, $context);
        $page->addExtension('helloRetailRecommendations', $data['recommendations']);

        $this->hook(new CheckoutOffcanvasWidgetLoadedHook($page, $context));

        return $this->renderStorefront(
            '@Storefront/storefront/component/checkout/offcanvas-cart.html.twig', [
                'page' => $page,
            ]
        );
    }

    private function getRecommendationsData(SalesChannelContext $context): array
    {
        $boxKey = $this->systemConfigService->getString('HelretHelloRetail.config.offcanvasCartKey');
        $recommendations = $this->helloRetailService->getRecommendations($boxKey, $context);

        return [
            'boxKey' => $boxKey,
            'recommendations' => $recommendations,
        ];
    }
}

