<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Service\Models\PageFilters;
use Helret\HelloRetail\Service\Models\PageParams;
use Helret\HelloRetail\Service\Models\PageProducts;
use Helret\HelloRetail\Service\Models\PageProductsResult;
use Helret\HelloRetail\Service\Models\Requests\PageRequest;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class HelloRetailPageService extends HelloRetailApiService
{
    private const STATIC_SEARCH_KEY = 'hello-retail-recommendations';

    private const endpoint = "pages";

    public function getPage(string $key, array $hierarchies, SalesChannelContext $salesChannelContext) : array
    {
        $urls = $this->renderUrls($salesChannelContext);

        return $this->fetchPage($key, $hierarchies, $urls);
    }

    private function fetchPage(string $key, array $hierarchies = [], $urls = []): array
    {
        $pageFilters = new PageFilters($hierarchies);
        $pageParams = new PageParams($pageFilters);
        //TODO
        //add start and count. Calculate from pagination of slot
        $pageProducts = new PageProducts(0, 100);
        $request = new PageRequest($pageParams, $pageProducts, $urls[0]);

        $callback = $this->client->callApi($this->buildEndpoint($key), $request);
        if (!$callback['success'] || empty($callback['products'])) {
            return [];
        }
        return $callback;
    }

    private function buildEndpoint(string $key): string
    {
        return self::endpoint . '/' . $key;
    }
}
