<?php

namespace Wexo\HelloRetail\Export;

class ExportEntity implements ExportEntityInterface
{
    private $storeFrontSalesChannelId;
    private $salesChannelDomainId;
    private $feeds;

    public function getStorefrontSalesChannelId()
    {
        return $this->storeFrontSalesChannelId;
    }

    public function getSalesChannelDomainId()
    {
        return $this->salesChannelDomainId;
    }

    public function getFeeds()
    {
        return $this->feeds;
    }

    public function setStoreFrontSalesChannelId($id)
    {
        $this->storeFrontSalesChannelId = $id;
    }

    public function setSalesChannelDomainId($id)
    {
        $this->salesChannelDomainId = $id;
    }

    public function setFeeds($feeds)
    {
        $this->feeds = $feeds;
    }
}
