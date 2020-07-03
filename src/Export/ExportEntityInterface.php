<?php

namespace Wexo\HelloRetail\Export;

interface ExportEntityInterface
{
    public function getStorefrontSalesChannelId();

    public function getSalesChannelDomainId();

    public function getFeeds();

    public function setStoreFrontSalesChannelId($id);

    public function setSalesChannelDomainId($id);

    public function setFeeds($feeds);
}
