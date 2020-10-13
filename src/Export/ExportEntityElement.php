<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Export;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ExportEntityElement
 * @package Wexo\HelloRetail\Export
 */
class ExportEntityElement
{
    /**
     * @var SalesChannelContext
     */
    protected $salesChannelContext;
    /**
     * @var string
     */
    protected $directory;
    /**
     * @var string
     */
    protected $id;
    /**
     * @var FeedEntityInterface
     */
    protected $feedEntity;
    /**
     * @var string
     */
    protected $entityType;
    /**
     * @var string
     */
    protected $templateType;
    /**
     * @var array|null
     */
    protected $allIds;
    /**
     * @var int
     */
    protected $retryCount = 0;

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
     * @param array $allIds
     */
    public function setAllIds(array $allIds)
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
