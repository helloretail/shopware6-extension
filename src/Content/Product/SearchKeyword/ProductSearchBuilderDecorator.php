<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Content\Product\SearchKeyword;

use Helret\HelloRetail\Service\HelloRetailConfigService;
use Helret\HelloRetail\Service\HelloRetailSearchService;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;

#[Package('system-settings')]
class ProductSearchBuilderDecorator implements ProductSearchBuilderInterface
{

    /**
     * @param ProductSearchBuilderInterface $decorated
     * @param HelloRetailSearchService $searchService
     * @param HelloRetailConfigService $helloRetailConfigService
     */
    public function __construct(
        private readonly ProductSearchBuilderInterface $decorated,
        private readonly HelloRetailSearchService $searchService,
        private readonly HelloRetailConfigService $helloRetailConfigService
    ) {}

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @param SalesChannelContext $context
     * @see ProductSearchBuilder::build()
     * @return void
     */
    public function build(Request $request, Criteria $criteria, SalesChannelContext $context): void
    {
        $search = $request->get('search');
        if ($this->helloRetailConfigService->hrSearchEnabled($context->getSalesChannelId())) {
            $offset = $criteria->getOffset();
            $limit = $criteria->getLimit();
            $idsFromHelloRetail = $this->searchService->search($search, $context, $offset, $limit);
            $criteria->setIds($idsFromHelloRetail ?? null);
            $criteria->resetSorting();
        }
        $this->decorated->build($request, $criteria, $context);
        return;
    }
}
