<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

/**
 * Interface FeedEntityInterface
 * @package Helret\HelloRetail\Export
 */
interface FeedEntityInterface
{
    /**
     * @return string
     */
    public function getFeed(): string;

    /**
     * @return string
     */
    public function getFeedDirectory(): string;

    /**
     * @return string
     */
    public function getFile(): string;

    /**
     * @return SalesChannelDomainEntity
     */
    public function getDomain(): SalesChannelDomainEntity;

    /**
     * @return array
     */
    public function getAssociations(): array;

    /**
     * @return string|null
     */
    public function getHeaderTemplate(): ?string;

    /**
     * @return string|null
     */
    public function getBodyTemplate(): ?string;

    /**
     * @return string|null
     */
    public function getFooterTemplate(): ?string;

    /**
     * @param string $feed
     */
    public function setFeed(string $feed): void;

    /**
     * @param string $feedDirectory
     */
    public function setFeedDirectory(string $feedDirectory): void;

    /**
     * @param string $file
     */
    public function setFile(string $file): void;

    /**
     * @param SalesChannelDomainEntity $salesChannelDomainEntity
     */
    public function setDomain(SalesChannelDomainEntity $salesChannelDomainEntity): void;

    /**
     * @param array $associations
     */
    public function setAssociations(array $associations): void;

    /**
     * @param string $template
     */
    public function setHeaderTemplate(string $template): void;

    /**
     * @param string $template
     */
    public function setBodyTemplate(string $template): void;

    /**
     * @param string $template
     */
    public function setFooterTemplate(string $template): void;

    public function getEntity(): string;

    public function setEntity(?string $entity): void;
}
