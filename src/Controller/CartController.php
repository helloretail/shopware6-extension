<?php declare(strict_types=1);

namespace Helret\HelloRetail\Controller;

use Helret\HelloRetail\Service\HelloRetailRecommendationService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\System\SystemConfig\SystemConfigService;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class CartController extends StorefrontController
{
    public function __construct(
        private readonly HelloRetailRecommendationService $helloRetailService,
        private readonly SystemConfigService $systemConfigService
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
    public function showOffcanvasCart(SalesChannelContext $context): Response
    {
        $boxKey = $this->systemConfigService->getString('HelretHelloRetail.config.offcanvasCartKey');

        $recommendations = $this->helloRetailService->getRecommendations($boxKey, $context);

        return $this->renderStorefront('@HelretHelloRetail/storefront/component/checkout/recommendations.html.twig', [
            'products' => $recommendations
        ]);
    }
}
