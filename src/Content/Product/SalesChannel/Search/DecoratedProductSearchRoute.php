<?php declare(strict_types=1);

namespace Helret\HelloRetail\Content\Product\SalesChannel\Search;

use Helret\HelloRetail\Service\HelloRetailSearchService;
use Helret\HelloRetail\Subscriber\SearchSubscriber;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class DecoratedProductSearchRoute extends AbstractProductSearchRoute
{
    /**
     * @param SalesChannelRepository<SalesChannelProductCollection> $productRepository
     */
    public function __construct(
        protected AbstractProductSearchRoute $decorated,
        protected HelloRetailSearchService $searchService,
        protected SalesChannelRepository $productRepository,
        protected Container $container
    ) {
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        return $this->decorated;
    }

    public function load(
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria
    ): ProductSearchRouteResponse {
        if (!$context->hasState(SearchSubscriber::SEARCH_AWARE)) {
            return $this->decorated->load($request, $context, $criteria);
        }

        $onlyAggregations = $request->request->get('only-aggregations');
        if ($onlyAggregations) {
            $response = $this->decorated->load(
                request: $request,
                context: $context,
                criteria: $criteria
            );
            $this->handleAggregations($response, $context, $criteria, $request);

            return $response;
        }

        $lazyAggregations = !$request->request->get('no-aggregations') &&
            !$request->request->get('reduce-aggregations');

        if (!$lazyAggregations) {
            return $this->decorated->load(
                request: $request,
                context: $context,
                criteria: $criteria
            );
        }

        $response = $this->decorated->load(
            request: $request,
            context: $context,
            criteria: $criteria
        );

        Profiler::trace(
            name: 'load-aggregation',
            closure: fn() => $this->handleAggregations(
                $response,
                $context,
                $criteria,
                $request
            ),
            category: 'hello-retail'
        );

        return $response;
    }


    protected function handleAggregations(
        ProductSearchRouteResponse $response,
        SalesChannelContext $context,
        Criteria $criteria,
        Request $request
    ): void {
        if (!$context->hasState(SearchSubscriber::SEARCH_AWARE)) {
            return;
        }

        $origin = clone $criteria;
        $origin->setLimit(0);
        $searchResponse = $this->searchService->searchByRequest(
            request: $request,
            criteria: $origin,
            context: $context,
            isFilterRequest: true
        );

        $aggregations = null;
        if ($searchResponse->getProducts()->getFilters()->current()) {
            $aggregations = new AggregationResultCollection();
            $searchResponse->getProducts()->getFilters()->rewind();
            foreach ($searchResponse->getProducts()->getFilters() as $filter) {
                $aggregation = $filter->getAsAggregationResult(
                    $this->container,
                    $context->getContext()
                );
                if ($aggregation) {
                    $aggregations->add($aggregation);
                }
            }
        } else {
            $origin->setLimit($searchResponse->getProducts()?->getTotalCount() ?: $criteria->getLimit());

            $searchResponse = $this->searchService->searchByRequest(
                request: $request,
                criteria: $origin,
                context: $context,
                isFilterRequest: true
            );
            if ($searchResponse->getProducts()) {
                $aggregations = $this->productRepository->aggregate(
                    criteria: $origin,
                    salesChannelContext: $context
                );
            }
        }

        if ($aggregations) {
            $response->getListingResult()->assign([
                'aggregations' => $aggregations,
            ]);
        }
    }
}
