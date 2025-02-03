<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\NestedEvent;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class HelretBeforeSearchEvent extends NestedEvent implements ShopwareSalesChannelEvent
{
    public function __construct(
        protected Request $request,
        protected SalesChannelContext $context,
        protected Criteria $criteria,
        protected array $postData,
        protected bool $isFilterRequest = false,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getPostData(): array
    {
        return $this->postData;
    }

    public function setPostData(array $postData): void
    {
        $this->postData = $postData;
    }

    public function changePostData(array $postData): void
    {
        $this->postData = array_replace($this->postData ?? [], $postData);
    }

    public function isFilterRequest(): bool
    {
        return $this->isFilterRequest;
    }
}
