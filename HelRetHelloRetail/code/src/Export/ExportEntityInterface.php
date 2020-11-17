<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Export;

/**
 * Interface ExportEntityInterface
 * @package Wexo\HelloRetail\Export
 */
interface ExportEntityInterface
{
    /**
     * @return string
     */
    public function getStorefrontSalesChannelId(): string;

    /**
     * @return string
     */
    public function getSalesChannelDomainId(): string;

    /**
     * @return array
     */
    public function getFeeds(): array;

    /**
     * @param string $id
     */
    public function setStoreFrontSalesChannelId(string $id): void;

    /**
     * @param string $id
     */
    public function setSalesChannelDomainId(string $id): void;

    /**
     * @param array $feeds
     */
    public function setFeeds(array $feeds): void;
}
