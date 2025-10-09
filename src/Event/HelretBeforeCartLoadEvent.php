<?php declare(strict_types=1);

namespace Helret\HelloRetail\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class HelretBeforeCartLoadEvent extends NestedEvent implements ShopwareSalesChannelEvent
{
    protected bool $shouldSkipCartLoad = false;

    public function __construct(protected array $ignored, protected SalesChannelContext $context)
    {
    }

    public function getIgnored(): array
    {
        return $this->ignored;
    }

    public function setIgnored(array $ignored): void
    {
        $this->ignored = $ignored;
    }

    public function shouldSkipCartLoad(): bool
    {
        return $this->shouldSkipCartLoad;
    }

    public function setShouldSkipCartLoad(bool $shouldSkipCartLoad): void
    {
        $this->shouldSkipCartLoad = $shouldSkipCartLoad;
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
