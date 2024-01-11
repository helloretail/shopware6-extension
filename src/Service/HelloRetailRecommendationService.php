<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Service\Models\RecommendationContext;
use Helret\HelloRetail\Service\Models\Requests\RecommendationRequest;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class HelloRetailRecommendationService extends HelloRetailApiService
{
    private const STATIC_SEARCH_KEY = 'hello-retail-recommendations';
    private const endpoint = "recoms";

    public function getRecommendationsSearch(
        string $key,
        string $searchKey,
        Entity $entity,
        SalesChannelContext $salesChannelContext = null
    ): CriteriaCollection {
        $collection = new CriteriaCollection();
        $hierarchies = $this->renderHierarchies($entity);
        $urls = $this->renderUrls($salesChannelContext);
        $productData = $this->fetchRecommendations($key, [$hierarchies], $urls);

        $ids = $this->getIds($productData);
        if (!$ids) {
            return $collection;
        }

        $criteria = new Criteria($ids);
        $criteria->addAssociation('cover');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('manufacturer');
        $criteria->addExtension('ids', new ArrayEntity([$ids]));
        $collection->add($searchKey, ProductDefinition::class, $criteria);

        return $collection;
    }

    public function getRecommendations(string $key): salesChannelProductCollection
    {
        $productData = $this->fetchRecommendations($key);
        return $this->getProducts($productData);
    }

    private function fetchRecommendations(string $key, array $hierarchies = [], $urls = []): array
    {
        $productData = [];
        $context = new RecommendationContext($hierarchies, "", $urls);
        $request = new Models\Recommendation($key, [self::extraData], $context);
        $request = new RecommendationRequest([$request]);

        $callback = $this->client->callApi(self::endpoint, $request);

        foreach ($callback['responses'] ?? [] as $response) {
            if (!$response['success']) {
                continue;
            }
            $productData = array_merge($productData, $response['products']);
        }
        return $productData;
    }
}
