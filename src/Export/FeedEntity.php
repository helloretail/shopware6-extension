<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class FeedEntity implements FeedEntityInterface
{
    private string $feed;
    private ?string $entity = null;
    private string $feedDirectory;
    private string $file;
    private SalesChannelDomainEntity $salesChannelDomainEntity;
    private array $associations = [];
    private ?string $headerTemplate = null;
    private ?string $bodyTemplate = null;
    private ?string $footerTemplate = null;

    public function getFeed(): string
    {
        return $this->feed;
    }

    public function getFeedDirectory(): string
    {
        return $this->feedDirectory;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getDomain(): SalesChannelDomainEntity
    {
        return $this->salesChannelDomainEntity;
    }

    public function getAssociations(): array
    {
        return $this->associations;
    }

    public function getHeaderTemplate(): ?string
    {
        return $this->headerTemplate;
    }

    public function getBodyTemplate(): ?string
    {
        return $this->bodyTemplate;
    }

    public function getFooterTemplate(): ?string
    {
        return $this->footerTemplate;
    }

    public function setFeed($feed): void
    {
        $this->feed = $feed;
    }

    public function setFeedDirectory(string $feedDirectory): void
    {
        $this->feedDirectory = $feedDirectory;
    }

    public function setFile($file): void
    {
        $this->file = $file;
    }

    public function setDomain(SalesChannelDomainEntity $salesChannelDomainEntity): void
    {
        $this->salesChannelDomainEntity = $salesChannelDomainEntity;
    }

    public function setAssociations($associations): void
    {
        $this->associations = $associations;
    }

    public function setHeaderTemplate($template): void
    {
        $this->headerTemplate = $template;
    }

    public function setBodyTemplate($template): void
    {
        $this->bodyTemplate = $template;
    }

    public function setFooterTemplate($template): void
    {
        $this->footerTemplate = $template;
    }

    public function setEntity(?string $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity(): string
    {
        return $this->entity ?: $this->feed;
    }
}
