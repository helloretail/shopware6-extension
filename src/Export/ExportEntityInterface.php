<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

interface ExportEntityInterface
{
    public function getStorefrontSalesChannelId(): string;

    public function getSalesChannelDomainId(): string;

    public function getFeeds(): array;

    public function setStoreFrontSalesChannelId(string $id): void;

    public function setSalesChannelDomainId(string $id): void;

    public function setFeeds(array $feeds): void;

    public function getFeedDirectory(): string;

    public function setFeedDirectory(string $feedDirectory): void;
}
