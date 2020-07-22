<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Export;

/**
 * Class ExportEntity
 * @package Wexo\HelloRetail\Export
 */
class ExportEntity implements ExportEntityInterface
{
    private string $storeFrontSalesChannelId;
    private string $salesChannelDomainId;
    private array $feeds;

    /**
     * @return string
     */
    public function getStorefrontSalesChannelId(): string
    {
        return $this->storeFrontSalesChannelId;
    }

    /**
     * @return string
     */
    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
    }

    /**
     * @return array
     */
    public function getFeeds(): array
    {
        return $this->feeds;
    }

    /**
     * @param string $id
     */
    public function setStoreFrontSalesChannelId(string $id): void
    {
        $this->storeFrontSalesChannelId = $id;
    }

    /**
     * @param string $id
     */
    public function setSalesChannelDomainId(string $id): void
    {
        $this->salesChannelDomainId = $id;
    }

    /**
     * @param array $feeds
     */
    public function setFeeds(array $feeds): void
    {
        $this->feeds = $feeds;
    }
}
