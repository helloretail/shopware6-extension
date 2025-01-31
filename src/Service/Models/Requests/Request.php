<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models\Requests;

class Request
{
    public function __construct(
        public ?string $websiteUuid,
        public ?string $trackingUserId
    ) {
    }

    public function getWebsiteUuid(): string
    {
        return $this->websiteUuid;
    }

    public function setWebsiteUuid(string $websiteUuid): void
    {
        $this->websiteUuid = $websiteUuid;
    }

    public function getTrackingUserId(): string
    {
        return $this->trackingUserId;
    }

    public function setTrackingUserId(string $trackingUserId): void
    {
        $this->trackingUserId = $trackingUserId;
    }
}
