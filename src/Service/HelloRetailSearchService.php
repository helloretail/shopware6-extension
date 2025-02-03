<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Event\HelretBeforeSearchEvent;
use Helret\HelloRetail\Models\CriteriaModel;
use Helret\HelloRetail\Models\SearchResponse;
use Helret\HelloRetail\Subscriber\SearchSubscriber;
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
        SalesChannelContext $context,
        bool $isFilterRequest = false
    ): SearchResponse {
        if (!$context->hasState(SearchSubscriber::SEARCH_AWARE)) {
            throw new RuntimeException('Context has to have a search state');
        }

        $response = $this->search(
            request: $request,
            context: $context,
            criteria: $criteria,
            isFilterRequest: $isFilterRequest
        );

        $productIds = $response->getProducts()?->getIds();
        if ($productIds) {
            $criteria->addExtension($response::NAME, $response);

            // Ensure that we actually find products as we might paginate
            $criteria->setOffset(0);

            // Set ids from HelloRetail and reset sorting to allow "IdSorting"
            $criteria->setIds($productIds);
            $criteria->resetSorting();

            // Bail on caching.
            if ($request->attributes->get(PlatformRequest::ATTRIBUTE_HTTP_CACHE)) {
                $request->attributes->set(PlatformRequest::ATTRIBUTE_HTTP_CACHE, false);
            }
        }

        return $response;
    }

    protected function search(
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria,
        bool $isFilterRequest = false
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

        // Setup aggregated data.
        if ($isFilterRequest) {
            /** @var SearchResponse|null $previousResponse */
            $previousResponse = $criteria->getExtensionOfType(SearchResponse::NAME, SearchResponse::class);

            $postData['products']['start'] = 0;
            $postData['products']['count'] = 0;
            $postData['products']['returnFilters'] = true; // Allows use of filtering via HR (Partial support)
            // Limit response data when searching for filter ids.
            $postData['products']['fields'] = ['extraData.id'];

            if (!$previousResponse->getProducts()->getFilters()->current()) {
                $postData['products']['returnFilters'] = false;
                $postData['products']['count'] = $previousResponse->getProducts()?->getTotalCount() ?: 5000;
            }
        }

        // Add filters
        if (!$isFilterRequest || $request->request->get('only-aggregations')) {
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

        // TODO: Sorting, once a key doesn't exist in HelloRetail NO products are found
        if ($request->get('order') && $request->get('order') !== 'score' &&
            $criteria->getSorting()
        ) {
            $fieldMap = [
                'product.name' => 'title',
                'product.cheapestPrice' => 'price',
                'id' => 'extraData.id',
                'product.releaseDate' => 'extraData.createdDate',
            ];

            $postData['products']['sorting'] = [];
            foreach ($criteria->getSorting() as $sorting) {
                $key = $fieldMap[$sorting->getField()] ?? $sorting->getField();
                $sort = strtolower($sorting->getDirection());
                $postData['products']['sorting'][] = "$key $sort";
            }
        }

        // Allow tampering
        $postData = $this->eventDispatcher->dispatch(
            new HelretBeforeSearchEvent(
                request: $request,
                context: $context,
                criteria: $criteria,
                postData: $postData,
                isFilterRequest: $isFilterRequest,
            ),
            'helret_before_search'
        )->getPostdata();

        $response = $this->client->callApi('search', $postData, salesChannelId: $context->getSalesChannelId());
        return new SearchResponse($response);
    }

    protected function getDefaultPostData(
        string $query,
        SalesChannelContext $context
    ): array {
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
                    'extraData.id',
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
                $map[$filter->getField()] = array_merge(
                    isset($map[$filter->getField()]) && is_array($map[$filter->getField()]) ?
                        $map[$filter->getField()] :
                        [],
                    $filter->getValue()
                );
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
