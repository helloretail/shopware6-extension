<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

class ExportEntityElement
{
    protected ?array $allIds;
    protected int $retryCount = 0;
    protected array $exportConfig = [];

    public function __construct(
        protected string $directory,
        protected string $id,
        protected FeedEntityInterface $feedEntity,
        protected string $entityType,
        protected string $templateType
    ) {
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFeedEntity(): FeedEntityInterface
    {
        return $this->feedEntity;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getTemplateType(): string
    {
        return $this->templateType;
    }

    public function getAllIds(): ?array
    {
        return $this->allIds;
    }

    public function setAllIds(?array $allIds): void
    {
        $this->allIds = $allIds;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $retryCount): void
    {
        $this->retryCount = $retryCount;
    }

    public function setExportConfig(array $exportConfig): void
    {
        $this->exportConfig = $exportConfig;
    }

    public function getExportConfig(): array
    {
        return $this->exportConfig;
    }

    public function getConfigValue(string $key, $default = null) : mixed
    {
        return $this->exportConfig[$key] ?? $default;
    }

    public function setConfigValue(string $key, $value): void
    {
        $this->exportConfig[$key] = $value;
    }
}
