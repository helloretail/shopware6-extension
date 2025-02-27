<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Service\Models\PageFilters;
use Helret\HelloRetail\Service\Models\PageParams;
use Helret\HelloRetail\Service\Models\Requests\PageRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class HelloRetailPageService extends HelloRetailApiService
{
    private const ENDPOINT = "pages";

    public function getPage(string $key, array $hierarchies, SalesChannelContext $salesChannelContext): array
    {
        return $this->fetchPage(
            $key,
            $salesChannelContext->getSalesChannelId(),
            $hierarchies,
            $this->renderUrls($salesChannelContext)
        );
    }

    private function fetchPage(string $key, string $salesChannelId, array $hierarchies = [], $urls = []): array
    {
        $pageFilters = new PageFilters($hierarchies);

        $request = new PageRequest($pageFilters, $urls[0]);
        $callback = $this->client->callApi(
            $this->buildEndpoint($key),
            $request,
            salesChannelId: $salesChannelId
        );
        if ($callback && (!$callback['success'] || empty($callback['products']))) {
            return [];
        }
        return $callback;
    }

    private function buildEndpoint(string $key): string
    {
        return self::ENDPOINT . '/' . $key;
    }
}
