<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Service\Models\RecommendationContext;
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


    public function getPage(string $key, Entity $entity, SalesChannelContext $salesChannelContext): salesChannelProductCollection
    {
        $hierarchies = $this->renderHierarchies($entity);
        $urls = $this->renderUrls($salesChannelContext);
        $productData = $this->fetchPage($key, $hierarchies, $urls);
        return $this->getProducts($productData);
    }

    private function fetchPage(string $key, array $hierarchies = [], $urls = []): array
    {
        $productData = [];
        $request = new Models\Recommendation($key, [self::extraData]);
        $callback = $this->client->callApi($this->buildEndpoint($key), $request);

        foreach ($callback['responses'] ?? [] as $response) {
            if (!$response['success']) {
                continue;
            }
            $productData = array_merge($productData, $response['products']);
        }
        return $productData;
    }

    private function buildEndpoint(string $key)
    {
        return self::endpoint . '/' . $key;
    }
}
