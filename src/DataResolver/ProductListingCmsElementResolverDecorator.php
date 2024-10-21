<?php declare(strict_types=1);

namespace Helret\HelloRetail\DataResolver;

use Helret\HelloRetail\Service\HelloRetailPageService;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\Cms\ProductListingCmsElementResolver;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;

class ProductListingCmsElementResolverDecorator extends ProductListingCmsElementResolver
{
    public function __construct(
        private readonly AbstractProductListingRoute $listingRoute,
        private readonly HelloRetailPageService $pageService
    ) {
        parent::__construct($this->listingRoute);
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return parent::collect($slot, $resolverContext);
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $hierarchies = [];

        $key = $config->get('helloRetailKey');

        if ($key) {
            if (method_exists($resolverContext, 'getEntity')) {
                $entity = $resolverContext->getEntity();
                $hierarchies = $this->pageService->renderHierarchies($entity);
            }

            $resolverContext->getRequest()->request->set('helloRetailPageKey', $key->getValue());
            $resolverContext->getRequest()->request->set('helloRetailHierarchies', $hierarchies);
        }

        parent::enrich($slot, $resolverContext, $result);
    }
}
