<?php

declare(strict_types=1);

namespace Helret\HelloRetail\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class HelretBeforeSearchEvent extends NestedEvent implements ShopwareSalesChannelEvent
{
    public function __construct(
        public array $postData,
        protected SalesChannelContext $context
    ) {
    }

    public function getPostData(): array
    {
        return $this->postData;
    }

    public function setPostData(array $postData): void
    {
        $this->postData = $postData;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }
}
