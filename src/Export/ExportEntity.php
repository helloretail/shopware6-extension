<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

class ExportEntity implements ExportEntityInterface
{
    private string $storeFrontSalesChannelId;
    private string $salesChannelDomainId;
    private array $feeds = [];
    private string $feedDirectory;

    public function getStorefrontSalesChannelId(): string
    {
        return $this->storeFrontSalesChannelId;
    }

    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
    }

    public function getFeeds(): array
    {
        return $this->feeds;
    }

    public function getFeedDirectory(): string
    {
        return $this->feedDirectory;
    }

    public function setStoreFrontSalesChannelId(string $id): void
    {
        $this->storeFrontSalesChannelId = $id;
    }

    public function setSalesChannelDomainId(string $id): void
    {
        $this->salesChannelDomainId = $id;
    }

    public function setFeeds(array $feeds): void
    {
        $this->feeds = $feeds;
    }

    public function setFeedDirectory(string $feedDirectory): void
    {
        $this->feedDirectory = $feedDirectory;
    }
}
