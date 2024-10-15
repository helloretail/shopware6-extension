<?php declare(strict_types=1);

namespace Helret\HelloRetail\Content\Product\SalesChannel\Listing;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Content\Product\SalesChannel\Listing\Processor\CompositeListingProcessor;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingFeaturesSubscriber;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRouteResponse;
use Shopware\Core\Content\Product\SalesChannel\Listing\ResolveCriteriaProductListingRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ResolvedCriteriaProductSearchRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResolveCriteriaHelloRetailListingRoute extends ResolveCriteriaProductListingRoute
{
    public function __construct(
        protected readonly AbstractProductListingRoute $decorated,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CompositeListingProcessor $processor
    ) {
        parent::__construct($this->decorated, $this->eventDispatcher, $this->processor);
    }

    #[Route(path: '/store-api/product-listing/{categoryId}', name: 'store-api.product.listing', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(string $categoryId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductListingRouteResponse
    {
        $criteria->addState(self::STATE);

        $this->processor->prepare($request, $criteria, $context);

        $context->getContext()->addState(ProductListingFeaturesSubscriber::HANDLED_STATE);

        $this->eventDispatcher->dispatch(
            new ProductListingCriteriaEvent($request, $criteria, $context)
        );

        $response = $this->getDecorated()->load($categoryId, $request, $context, $criteria);

        $response->getResult()->addCurrentFilter('navigationId', $categoryId);

        $this->processor->process($request, $response->getResult(), $context);

        $this->eventDispatcher->dispatch(
            new ProductListingResultEvent($request, $response->getResult(), $context)
        );

        $response->getResult()->getAvailableSortings()->removeByKey(
            ResolvedCriteriaProductSearchRoute::DEFAULT_SEARCH_SORT
        );

        return $response;
    }
}