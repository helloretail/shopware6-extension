<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Service\Models\PageFilters;
use Helret\HelloRetail\Service\Models\PageParams;
use Helret\HelloRetail\Service\Models\Requests\PageRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class HelloRetailPageService extends HelloRetailApiService
{
    private const ENDPOINT = "pages";

    public function getPage(string $key, array $hierarchies, SalesChannelContext $salesChannelContext) : array
    {
        $urls = $this->renderUrls($salesChannelContext);

        return $this->fetchPage($key, $hierarchies, $urls);
    }
    
    private function fetchPage(string $key, array $hierarchies = [], $urls = []): array
    {
        $pageFilters = new PageFilters($hierarchies);
        $pageParams = new PageParams($pageFilters);
        // this is not doing anything but it is required by HR
        $productOffset = ['start' => 0, 'count' => 100];

        $request = new PageRequest($pageParams, $urls[0], $productOffset);
        $callback = $this->client->callApi($this->buildEndpoint($key), $request);
        if (!$callback['success'] || empty($callback['products'])) {
            return [];
        }
        return $callback;
    }
    
    private function buildEndpoint(string $key): string
    {
        return self::ENDPOINT . '/' . $key;
    }
}
