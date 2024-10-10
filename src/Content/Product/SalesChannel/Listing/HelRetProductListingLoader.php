<?php declare(strict_types=1);

namespace Helret\HelloRetail\Content\Product\SalesChannel\Listing;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HelRetProductListingLoader extends ProductListingLoader
{

    public function load(Criteria $origin, SalesChannelContext $context): EntitySearchResult
    {
        $originResult = parent::load($origin, $context);
        //TODO
        $origin->setOffset(8);
        return new EntitySearchResult($originResult->getEntity(), 14, $originResult->getEntities(), $originResult->getAggregations(), $origin, $context->getContext());
    }

}
