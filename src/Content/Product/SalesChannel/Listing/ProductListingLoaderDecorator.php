<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Content\Product\SalesChannel\Listing;

use Helret\HelloRetail\Service\HelloRetailConfigService;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('inventory')]
class ProductListingLoaderDecorator extends ProductListingLoader
{
    protected ProductListingLoader $decorated;
    protected HelloRetailConfigService $helloRetailConfigService;

    public function setDecorated(ProductListingLoader $decorated): void
    {
        $this->decorated = $decorated;
    }

    public function setConfigService(HelloRetailConfigService $helloRetailConfigService): void
    {
        $this->helloRetailConfigService = $helloRetailConfigService;
    }

    /**
     * @return EntitySearchResult<ProductCollection>
     */
    public function load(Criteria $origin, SalesChannelContext $context): EntitySearchResult
    {
        $result = $this->decorated->load($origin, $context);
        $ids = $origin->getIds();
        $result->getEntities()->sortByIdArray($ids);
        return $result;
    }
}
