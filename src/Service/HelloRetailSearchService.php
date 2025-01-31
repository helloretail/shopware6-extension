<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Event\HelretBeforeSearchEvent;
use Helret\HelloRetail\Models\CriteriaModel;
use Helret\HelloRetail\Models\SearchResponse;
use Helret\HelloRetail\Subscriber\SearchSubscriber;
use http\Exception\InvalidArgumentException;
use RuntimeException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\Saleschannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HelloRetailSearchService
{
    public function __construct(
        protected readonly HelloRetailClientService $client,
        protected readonly SystemConfigService $configService,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly HelloRetailApiService $helloRetailApiService,
        protected readonly HelloRetailRecommendationService $helloRetailRecommendationService
    ) {
    }

    public function searchByRequest(
        Request $request,
        Criteria $criteria,
        SalesChannelContext $context
    ): SearchResponse {
        if (!$context->hasState(SearchSubscriber::SEARCH_AWARE)) {
            throw new RuntimeException('Context has to have a search state');
        }

        $response = $this->search(request: $request, context: $context, criteria: $criteria);

        $productIds = $response->getProducts()?->getIds();
        if ($productIds) {
            $criteria->addExtension($response::NAME, $response);

            // Ensure that we actually find products as we might paginate
            $criteria->setOffset(0);

            // Set ids from HelloRetail and reset sorting to allow "IdSorting"
            $criteria->setIds($productIds);
            $criteria->resetSorting();

            if ($request->attributes->get(PlatformRequest::ATTRIBUTE_HTTP_CACHE)) {
                $request->attributes->set(PlatformRequest::ATTRIBUTE_HTTP_CACHE, false);
            }
        }

        return $response;
    }

    protected function search(
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria
    ): SearchResponse {
        $query = trim((string)$request->get('search'));
        if (!$query) {
            throw RoutingException::missingRequestParameter('search');
        }

        $limit = $criteria->getLimit();
        $offset = $criteria->getOffset();

        /** @var CriteriaModel|null $criteriaModel */
        $criteriaModel = $criteria->getExtensionOfType(CriteriaModel::NAME, CriteriaModel::class);
        if ($criteriaModel) {
            $limit = $criteriaModel->getLimit();
            $offset = $criteriaModel->getOffset() ?: 0;
        }

        $postData = $this->getDefaultPostData($query, $context);
        $postData['products']['start'] = $offset ?: 0;
        $postData['products']['count'] = $limit;

        /**
         * TODO; Initial search doesn't work as expected.
         *  /search?limit=9&min-price=5000&order=price-asc&p=1&search=test
         *  Result is first found at page 3.
         *
         * Once the page/filter has been applied "no-worries" but initial load skips that part.
         * TLDR; Ensure filtered content also work on initial load with aggregations
         */
        if (!$request->get('only-aggregations') && !$criteria->getAggregations()) {
            $postData['products']['filters'] = [];
            foreach ($this->mapFilters($criteria->getPostFilters()) as $field => $map) {
                if ($field === 'product.manufacturerId') {
                    $postData['products']['filters'] = array_merge(
                        $postData['products']['filters'],
                        array_map(
                            fn(string $id) => "extraData.manufacturerId:$id",
                            $map
                        )
                    );
                } elseif ($field === 'product.propertyIds') {
                    $postData['products']['filters'] = array_merge(
                        $postData['products']['filters'],
                        array_map(
                            fn(string $id) => "extraDataList.propertyIds:$id",
                            $map
                        )
                    );
                } elseif ($field === 'product.cheapestPrice') {
                    $postData['products']['filters'][] = "price:$map";
                }
            }
        }

        if ($request->get('order') && $request->get('order') !== 'score' &&
            $criteria->getSorting()
        ) {
            $fieldMap = [
                'product.name' => 'title',
                'product.cheapestPrice' => 'price',
                'id' => 'extraData.id',
            ];

            $postData['products']['sorting'] = [];
            foreach ($criteria->getSorting() as $sorting) {
                $key = $fieldMap[$sorting->getField()] ?? $sorting->getField();
                $sort = strtolower($sorting->getDirection());
                $postData['products']['sorting'][] = "$key $sort";
            }
        }

        $postData = $this->eventDispatcher->dispatch(
            new HelretBeforeSearchEvent($postData, $context),
            'helret_before_search'
        )->getPostdata();

        $response = $this->client->callApi('search', $postData, salesChannelId: $context->getSalesChannelId());
        return new SearchResponse($response);
    }

    public function getDefaultPostData(string $query, SalesChannelContext $context): array
    {
        return [
            'query' => $query,
            'key' => $this->configService->getString(
                'HelretHelloRetail.config.searchConfigKey',
                $context->getSalesChannelId()
            ),
            'products' => [
                'returnFilters' => false,
                'start' => 0,
                'fields' => [
                    'title',
                    'extraData.id',
                    'extraData.productId',
                ],
            ],
            'format' => 'json',
        ];
    }

    /**
     * @param array<Filter> $filters
     * @return array<string, array|string|mixed>
     */
    protected function mapFilters(array $filters, &$map = []): array
    {
        foreach ($filters as $filter) {
            if ($filter instanceof EqualsAnyFilter) {
                $map[$filter->getField()] = $filter->getValue();
            } elseif ($filter instanceof EqualsFilter) {
                $map[$filter->getField()] = $filter->getValue();
            } elseif ($filter instanceof RangeFilter) {
                $params = array_merge([
                    'gte' => null,
                    'lte' => null,
                ], $filter->getParameters());

                $map[$filter->getField()] = implode(',', $params);
            } elseif ($filter instanceof MultiFilter) {
                $this->mapFilters($filter->getQueries(), $map);
            }
        }

        return $map;
    }
}
