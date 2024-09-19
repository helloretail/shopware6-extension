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

    private const id = "extraData.id";
    private const endpoint = "pages";
    private const displayFilter = "extraData.display:true";
    private const displayGroup = 'extraData.displayGroup';

    public function getPage(string $key, array $hierarchies, SalesChannelContext $salesChannelContext): PageProductsResult
    {
        $urls = $this->renderUrls($salesChannelContext);
        $productData = $this->fetchPage($key, $hierarchies, $urls);
        $ids = $this->getIds($productData['result'] ?? []);

        return new PageProductsResult(
            $productData['start'],
            $productData['count'],
            $productData['total'],
            [
                '01906e31740f78f9b0883ec3e8c4a059',
                '02317d6559024e48949120c3290695f4',
                '1901dc5e888f4b1ea4168c2c5f005540',
                '11dc680240b04f469ccba354cbf0b967'
            ]
        );
    }

    private function fetchPage(string $key, array $hierarchies = [], $urls = []): array
    {
        $pageFilters = new PageFilters($hierarchies);
        $pageParams = new PageParams($pageFilters);
        //TODO
        //add start and count. Calculate from pagination of slot
        $pageProducts = new PageProducts(0, 100, [self::id, self::displayGroup], [self::displayFilter]);
        $request = new PageRequest($pageParams, $pageProducts, $urls[0]);
        //$request = new PageRequest($key, [self::extraData]);
        $callback = $this->client->callApi($this->buildEndpoint($key), $request);

        if (!$callback['success'] || empty($callback['products'])) {
            return [];
        }

        return $callback['products'];
    }

    private function buildEndpoint(string $key): string
    {
        return self::endpoint . '/' . $key;
    }
}
