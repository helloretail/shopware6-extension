<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Event\HelretBeforeSearchEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\Saleschannel\SalesChannelContext;

class HelloRetailSearchService
{
    /**
     * @param HelloRetailClientService $client
     * @param HelloRetailConfigService $helloRetailConfigService
     * @param EventDispatcherInterface $eventDispatcher
     * @param HelloRetailApiService $helloRetailApiService
     * @param HelloRetailRecommendationService $helloRetailRecommendationService
     */
    public function __construct(
        protected readonly HelloRetailClientService $client,
        protected readonly HelloRetailConfigService $helloRetailConfigService,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly HelloRetailApiService $helloRetailApiService,
        protected readonly HelloRetailRecommendationService $helloRetailRecommendationService
    ) {}

    /**
     * @param string $query
     * @param SalesChannelContext $context
     * @return array
     */
    public function search(string $query, SalesChannelContext $context, int $offset = 0, int $limit = 1000): array
    {
        $postData = $this->getDefaultPostData($query, $context);
        $postData['products']['start'] = $offset;
        $postData['products']['count'] = $limit;

        $postData = $this->eventDispatcher->dispatch(
            new HelretBeforeSearchEvent($postData, $context),
            'helret_before_search'
        )->getPostdata();

        $response = $this->client->callApi('search', $postData);
        return $this->helloRetailApiService->getIds($response['products']['results']);
    }


    /**
     * @param string $query
     * @param SalesChannelContext $context
     * @return array
     */
    public function suggest(string $query, SalesChannelContext $context): array
    {
        $postData = $this->getDefaultPostData($query, $context);
        $postData['products']['count'] = $this->helloRetailConfigService->getProductCount($context->getSalesChannelId());

        if ($this->helloRetailConfigService->getEnabledCategory($context->getSalesChannelId())) {
            $postData['categories'] = [
                'returnFilters' => false,
                'start' => 0,
                'count' => $this->helloRetailConfigService->getCategoryCount($context->getSalesChannelId()),
                'fields' => ['title']
            ];
        }

        $postData = $this->eventDispatcher->dispatch(
            new HelretBeforeSearchEvent($postData, $context),
            'helret_before_search_overlay'
        )->getPostdata();

        return $this->client->callApi('search', $postData);
    }

    /**
     * @param string $query
     * @param SalesChannelContext $context
     * @return array
     */
    public function getDefaultPostData(string $query, SalesChannelContext $context): array
    {
        return [
            'query' => $query,
            'key' => $this->helloRetailConfigService->getSearchConfigKey($context->getSalesChannelId()),
            'products' => [
                'returnFilters' => false,
                'start' => 0,
                'fields' => ['title', 'extraData.productId', 'extraData.id']
            ],
            'format' => 'json'
        ];
    }
}
