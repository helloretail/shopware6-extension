<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class HelloRetailRecommendationService
{
    private const productNumber = "productNumber";
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

    public function getRecommendations(string $key, Context $context): salesChannelProductCollection
    {
        $productData = [];
        $request = new Models\Recommendation($key, self::productNumber);
        $callback = $this->client->callApi($request, self::endpoint);
        foreach ($callback['responses'] as $response) {
            $productData = array_merge($productData, $response['products']);
        }
        return $this->getProducts($productData);
    }

    private function getProducts($productData)
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter(
                self::productNumber,
                array_column($productData,
                    'productNumber'),
            ),
        );
        return $this->productRepository->search($criteria, Context::createDefaultContext());
    }


}
