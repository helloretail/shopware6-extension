<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Content\Product\SalesChannel\Listing;

use Helret\HelloRetail\Models\SearchResponse;
use Helret\HelloRetail\Subscriber\SearchSubscriber;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DecoratedProductListingLoader extends ProductListingLoader
{
    protected ProductListingLoader $decorated;

    public function setDecorated(ProductListingLoader $decorated): void
    {
        $this->decorated = $decorated;
    }

    /**
     * @return EntitySearchResult<ProductCollection>
     */
    public function load(Criteria $origin, SalesChannelContext $context): EntitySearchResult
    {
        if (!$context->hasState(SearchSubscriber::SEARCH_AWARE)) {
            return $this->decorated->load($origin, $context);
        }

        $result = $this->decorated->load($origin, $context);
        if ($origin->getAggregations() && $origin->getPostFilters()) {
            // To allow correct filtering options, we need to do an extra request without the filters/aggregations
            $criteria = clone $origin;

            // no total count required
            $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_NONE);

            // sorting and association are only required for the product data
            $criteria->resetSorting();
            $criteria->resetAssociations();

            $criteria->setLimit(0);
            $criteria->setOffset(0);
            $criteria->resetAggregations();

            $nonAggregated = $this->decorated->load($origin, $context);
            $result->assign([
                'aggregations' => $nonAggregated->getAggregations(),
                'total' => $nonAggregated->getTotal(),
            ]);
        }

        /** @var SearchResponse|null $search */
        $search = $result->getCriteria()->getExtensionOfType(SearchResponse::NAME, SearchResponse::class);
        if ($search) {
            // Reset offset to set correct pagination
            $result->getCriteria()->setOffset($search->getProducts()->getStart());

            // Set total to allow pagination and show on page.
            $result->assign([
                'total' => $search->getProducts()->getTotalCount()
            ]);
        }

        return $result;
    }
}
