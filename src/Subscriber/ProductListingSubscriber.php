<?php declare(strict_types=1);

namespace Helret\HelloRetail\Subscriber;

use Helret\HelloRetail\Event\HelretBeforeCartLoadEvent;
use Helret\HelloRetail\HelretHelloRetail;
use Helret\HelloRetail\Service\HelloRetailPageService;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductListingResolvePreviewEvent;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Checkout\Cart\SalesChannel\StorefrontCartFacade;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SalesChannel\SalesChannelEvents;

class ProductListingSubscriber implements EventSubscriberInterface
{
    protected const helloRetailProductIds = 'helloRetailProductIds';
    protected const order = 'topseller';

    public function __construct(
        protected HelloRetailPageService $pageService,
        protected EntityRepository $productRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCriteriaEvent::class => 'onProductListingCriteria',
            ProductListingResultEvent::class => 'onProductListingResult',
            ProductListingResolvePreviewEvent::class => 'onProductListingResolvePreview'
        ];
    }

    public function onProductListingCriteria(ProductListingCriteriaEvent $event)
    {
        $foo = $event;
        $request = $event->getRequest();
        $pageKey = $request->get('helloRetailPageKey');
        if (!$pageKey) {
            return;
        }
        $hierarchies = $request->get('helloRetailHierarchies') ?? [];

        $pageProductsResult = $this->pageService->getPage($pageKey, $hierarchies, $event->getSalesChannelContext());

        $criteria = $event->getCriteria();
        $criteria->setIds($pageProductsResult->getProductIds());
        //$criteria->addFilter(new EqualsAnyFilter('id', $pageProductsResult->getProductIds()));
        //$criteria->addSorting(new FieldSorting())

        //if ($)

//        $request->attributes->set('helloRetailProductIds', $pageProductsResult->getProductIds());

        if ($request->get('order') != self::order) {
            return;
        }
        $criteria->addExtension(self::helloRetailProductIds, new ArrayEntity($pageProductsResult->getProductIds()));
    }

    public function onProductListingResult(ProductListingResultEvent $event)
    {
        $foo = $event;
        if (!$event->getResult()->getTotal()) {
            return;
        }
        $idsOrder = $event->getResult()->getCriteria()->getIds() ?? null;
        //TODO MAKE SMARTER
        if (!$idsOrder || $event->getResult()->getSorting() != self::order) {
            return;
        }
        //$event->getResult()->set('helloRetailProductIds', $idsOrder);
        $event->getResult()->sortByIdArray($idsOrder);
        $foo = 1;
    }

    public function onProductListingResolvePreview(ProductListingResolvePreviewEvent $event)
    {
        $origin = $event->getCriteria();
        $criteria = clone $origin;
        $helloRetailIds = $criteria->getExtension(self::helloRetailProductIds)->all();
        if (!$helloRetailIds) {
            return;
        }
        $offset = $criteria->getOffset();
        $limit = $criteria->getLimit();
        $criteria->setOffset(null);
        $criteria->setLimit(null);
        $criteria->resetSorting();
        $result = $this->productRepository->searchIds($criteria, $event->getContext());

        $mapping = $event->getMapping();
        $slice = array_slice($result->getIds(), $offset, $limit);
        $i = 0;
        foreach ($mapping as $key => $value) {
            $event->replace($key, $slice[$i]);
            $i++;
        }


        $origin->setIds($slice);
    }
}
