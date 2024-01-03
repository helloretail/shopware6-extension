<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Helret\HelloRetail\Service\Models\RecommendationContext;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\CookieController;

class HelloRetailRecommendationService
{
    private const STATIC_SEARCH_KEY = 'hello-retail-recommendations';

    private const extraData = "extraData";
    private const endpoint = "recoms";

    /**
     * @param HelloRetailClientService $client
     * @param EntityRepository $productRepository
     */
    public function __construct(
        protected HelloRetailClientService $client,
        protected EntityRepository $productRepository
    ) {
    }

    public function getRecommendationsSearch(
        string $key,
        string $searchKey,
        CategoryEntity $category = null,
        SalesChannelContext $salesChannelContext = null
    ): ?CriteriaCollection {
        $collection = new CriteriaCollection();
        $hierarchies = [];
        $urls = [];
        if ($category && $category->getBreadcrumb() > 0) {
            $hierarchies = $category->getBreadcrumb();
        }
        if ($salesChannelContext) {
            /** @var SalesChannelDomainEntity $domain */
            foreach ($salesChannelContext->getSalesChannel()->getDomains() as $domain) {
                $urls[] = $domain->getUrl();
            }
        }
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
        $callback = $this->client->callApi(self::endpoint, $request);

        foreach ($callback['responses'] ?? [] as $response) {
            if (!$response['success']) {
                continue;
            }
            $productData = array_merge($productData, $response['products']);
        }
        return $productData;
    }

    private function getProducts(array $productData): mixed
    {
        $ids = $this->getIds($productData);
        if (!$ids) {
            return null;
        }

        $criteria = new Criteria($ids);
        return $this->productRepository->search($criteria, Context::createDefaultContext())->getEntities();
    }

    private function getIds(array $productData): array
    {
        $ids = [];
        foreach ($productData as $data) {
            if (isset($data[self::extraData]['id'])) {
                $ids[] = $data[self::extraData]['id'];
            }
        }

        return $ids;
    }
}
