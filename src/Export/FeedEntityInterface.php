<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export;

interface FeedEntityInterface
{
    public function getFeed(): string;

    public function getFeedDirectory(): string;

    public function getFile(): string;

    public function getDomain(): string;

    public function getAssociations(): array;

    public function getHeaderTemplate(): ?string;

    public function getBodyTemplate(): ?string;

    public function getFooterTemplate(): ?string;

    public function setFeed(string $feed): void;

    public function setFeedDirectory(string $feedDirectory): void;

    public function setFile(string $file): void;

    public function setDomain(string $salesChannelDomainId): void;

    public function setAssociations(array $associations): void;

    public function setHeaderTemplate(string $template): void;

    public function setBodyTemplate(string $template): void;

    public function setFooterTemplate(string $template): void;

    public function getEntity(): string;

    public function setEntity(?string $entity): void;
}
