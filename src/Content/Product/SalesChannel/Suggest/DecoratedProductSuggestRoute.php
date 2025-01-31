<?php declare(strict_types=1);

namespace Helret\HelloRetail\Content\Product\SalesChannel\Suggest;

use Helret\HelloRetail\Service\HelloRetailSearchService;
use Helret\HelloRetail\Subscriber\SearchSubscriber;
use Shopware\Core\Content\Product\SalesChannel\Suggest\AbstractProductSuggestRoute;
use Shopware\Core\Content\Product\SalesChannel\Suggest\ProductSuggestRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DecoratedProductSuggestRoute extends AbstractProductSuggestRoute
{
    public function __construct(
        protected AbstractProductSuggestRoute $decorated,
        protected HelloRetailSearchService $searchService
    ) {
    }

    public function getDecorated(): AbstractProductSuggestRoute
    {
        return $this->decorated;
    }

    public function load(
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria
    ): ProductSuggestRouteResponse {
        if ($context->hasState(SearchSubscriber::SEARCH_AWARE)) {
            $response = $this->searchService->searchByRequest(
                request: $request,
                criteria: $criteria,
                context: $context
            );
            if ($criteria->hasExtensionOfType($response::NAME, $response::class)) {
                $criteria->resetFilters();
                $criteria->resetQueries();
            } else {
                $context->removeState(SearchSubscriber::SEARCH_AWARE);
            }
        }

        return $this->decorated->load($request, $context, $criteria);
    }
}
