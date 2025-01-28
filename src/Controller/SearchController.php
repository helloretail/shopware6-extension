<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Controller;

use Helret\HelloRetail\Service\HelloRetailSearchService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route(defaults: ['_routeScope' => ['storefront']])]
class SearchController extends StorefrontController
{
    public function __construct(
        private readonly HelloRetailSearchService $searchService,
    ) {
    }

    #[Route(
        path: '/helretsuggest',
        name: 'widgets.hello.retail.suggest',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true],
        methods: ['GET']
    )]
    public function SuggestHelloRetail(SalesChannelContext $context, Request $request): Response
    {
        $search = $request->query->get('search');
        $response = $this->searchService->suggest($search, $context);

        $products = $response['products'] ?? null;
        $categories = $response['categories'] ?? null;

        return $this->renderstorefront('@helrethelloretail/storefront/layout/header/search-suggest-helret.html.twig', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
}
