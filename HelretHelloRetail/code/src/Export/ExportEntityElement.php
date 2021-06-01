<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ExportEntityElement
 * @package Helret\HelloRetail\Export
 */
class ExportEntityElement
{
    protected SalesChannelContext $salesChannelContext;
    protected string $directory;
    protected string $id;
    protected FeedEntityInterface $feedEntity;
    protected string $entityType;
    protected string $templateType;
    protected ?array $allIds;
    protected int $retryCount = 0;

    /**
     * ExportEntityElement constructor.
     * @param SalesChannelContext $salesChannelContext
     * @param string $directory
     * @param string $id
     * @param FeedEntityInterface $feedEntity
     * @param string $entityType
     * @param string $templateType
     */
    public function __construct(
        SalesChannelContext $salesChannelContext,
        string $directory,
        string $id,
        FeedEntityInterface $feedEntity,
        string $entityType,
        string $templateType
    ) {
        $this->salesChannelContext = $salesChannelContext;
        $this->directory = $directory;
        $this->id = $id;
        $this->feedEntity = $feedEntity;
        $this->entityType = $entityType;
        $this->templateType = $templateType;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return FeedEntityInterface
     */
    public function getFeedEntity(): FeedEntityInterface
    {
        return $this->feedEntity;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @return string
     */
    public function getTemplateType(): string
    {
        return $this->templateType;
    }

    /**
     * @return array|null
     */
    public function getAllIds(): ?array
    {
        return $this->allIds;
    }

    /**
     * @param array|null $allIds
     */
    public function setAllIds(array $allIds): ?array
    {
        $this->allIds = $allIds;
    }

    /**
     * @return int
     */
    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    /**
     * @param int $retryCount
     */
    public function setRetryCount(int $retryCount)
    {
        $this->retryCount = $retryCount;
    }
}
