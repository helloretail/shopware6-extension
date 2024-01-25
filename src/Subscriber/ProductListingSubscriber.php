<?php declare(strict_types=1);

namespace Helret\HelloRetail\Subscriber;

use Helret\HelloRetail\Service\HelloRetailPageService;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductListingResolvePreviewEvent;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductListingSubscriber implements EventSubscriberInterface
{
    protected const helloRetailProductIds = 'helloRetailProductIds';
    protected const order = 'topseller';

    protected $helloRetailIds = null;

    public function __construct(
        protected HelloRetailPageService $pageService,
        protected EntityRepository $productRepository,
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
        $request = $event->getRequest();
        $pageKey = $request->get('helloRetailPageKey');
        if (!$pageKey) {
            return;
        }
        $hierarchies = $request->get('helloRetailHierarchies') ?? [];

        $pageProductsResult = $this->pageService->getPage($pageKey, $hierarchies, $event->getSalesChannelContext());

        $criteria = $event->getCriteria();
        $ids = $pageProductsResult->getProductIds();

        $criteria->addExtension('pagination', new ArrayEntity(['offset' => $criteria->getOffset(), 'limit' => $criteria->getLimit()]));

        $criteria->setIds(array_slice($ids, $criteria->getOffset(), $criteria->getLimit()));
        $criteria->setOffset(0);
        $criteria->addFilter(new EqualsAnyFilter('id', $pageProductsResult->getProductIds()));

        if ($request->get('order') != self::order) {
            return;
        }
        $criteria->addExtension(self::helloRetailProductIds, new ArrayEntity($pageProductsResult->getProductIds()));

        $this->helloRetailIds = $pageProductsResult->getProductIds();
    }

    public function onProductListingResult(ProductListingResultEvent $event)
    {
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
    }

    public function onProductListingResolvePreview(ProductListingResolvePreviewEvent $event)
    {
        return;
        $origin = $event->getCriteria();
        $criteria = clone $origin;
        $helloRetailIds = $criteria->getExtension(self::helloRetailProductIds)->all() ?? [];
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
