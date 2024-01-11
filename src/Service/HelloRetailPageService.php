<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Service\Models\PageFilters;
use Helret\HelloRetail\Service\Models\PageParams;
use Helret\HelloRetail\Service\Models\PageProducts;
use Helret\HelloRetail\Service\Models\RecommendationContext;
use Helret\HelloRetail\Service\Models\Requests\PageRequest;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class HelloRetailPageService extends HelloRetailApiService
{
    private const STATIC_SEARCH_KEY = 'hello-retail-recommendations';

    private const id = "extraData.id";
    private const endpoint = "pages";

    public function getPage(string $key, Entity $entity, SalesChannelContext $salesChannelContext): array
    {
        $hierarchies = $this->renderHierarchies($entity);
        $urls = $this->renderUrls($salesChannelContext);
        return $this->fetchPage($key, $hierarchies, $urls);
    }

    private function fetchPage(string $key, array $hierarchies = [], $urls = []): array
    {
        $pageFilters = new PageFilters($hierarchies);
        $pageParams = new PageParams($pageFilters);
        //TODO
        //add start and count. Calculate from pagination of slot
        $pageProducts = new PageProducts(0, 20, [self::id]);
        $request = new PageRequest($pageParams, $pageProducts, $urls[0]);
        //$request = new PageRequest($key, [self::extraData]);
        $callback = $this->client->callApi($this->buildEndpoint($key), $request);

        if (!$callback['success'] || empty($callback['products'])) {
            return [];
        }

        return $callback['products'];
    }

    private function buildEndpoint(string $key)
    {
        return self::endpoint . '/' . $key;
    }
}
