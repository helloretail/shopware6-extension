<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class FeedEntity implements FeedEntityInterface, AsyncMessageInterface
{
    private string $feed;
    private ?string $entity = null;
    private string $feedDirectory;
    private string $file;
    private string $salesChannelDomainId;
    private string $salesChannelDomainLanguageId;
    private string $salesChannelDomainLanguageLocaleId;
    private string $salesChannelDomainCurrencyId;
    private string $salesChannelId;
    private string $salesChannelDomainUrl;
    private array $associations = [];
    private ?string $headerTemplate = null;
    private ?string $bodyTemplate = null;
    private ?string $footerTemplate = null;

    public function getSalesChannelDomainUrl(): string
    {
        return $this->salesChannelDomainUrl;
    }

    public function setSalesChannelDomainUrl(string $salesChannelDomainUrl): void
    {
        $this->salesChannelDomainUrl = $salesChannelDomainUrl;
    }

    public function getSalesChannelDomainLanguageId(): string
    {
        return $this->salesChannelDomainLanguageId;
    }

    public function setSalesChannelDomainLanguageId(string $salesChannelDomainLanguageId): void
    {
        $this->salesChannelDomainLanguageId = $salesChannelDomainLanguageId;
    }

    public function getSalesChannelDomainLanguageLocaleId(): string
    {
        return $this->salesChannelDomainLanguageLocaleId;
    }

    public function setSalesChannelDomainLanguageLocaleId(string $salesChannelDomainLanguageLocaleId): void
    {
        $this->salesChannelDomainLanguageLocaleId = $salesChannelDomainLanguageLocaleId;
    }

    public function getSalesChannelDomainCurrencyId(): string
    {
        return $this->salesChannelDomainCurrencyId;
    }

    public function setSalesChannelDomainCurrencyId(string $salesChannelDomainCurrencyId): void
    {
        $this->salesChannelDomainCurrencyId = $salesChannelDomainCurrencyId;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

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

    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
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

    public function setSalesChannelDomainId(string $salesChannelDomainId): void
    {
        $this->salesChannelDomainId = $salesChannelDomainId;
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