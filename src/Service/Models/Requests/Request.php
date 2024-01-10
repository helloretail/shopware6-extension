<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models\Requests;

class Request {
    public ?string $websiteUuid;
    public ?string $trackingUserId;

    /**
     * @param ?string $websiteUuid
     * @param ?string $trackingUserId
     */
    public function __construct(?string $websiteUuid, ?string $trackingUserId)
    {
        $this->websiteUuid = $websiteUuid;
        $this->trackingUserId = $trackingUserId;
    }

    /**
     * @return string
     */
    public function getWebsiteUuid(): string
    {
        return $this->websiteUuid;
    }

    /**
     * @param string $websiteUuid
     */
    public function setWebsiteUuid(string $websiteUuid): void
    {
        $this->websiteUuid = $websiteUuid;
    }

    /**
     * @return string
     */
    public function getTrackingUserId(): string
    {
        return $this->trackingUserId;
    }

    /**
     * @param string $trackingUserId
     */
    public function setTrackingUserId(string $trackingUserId): void
    {
        $this->trackingUserId = $trackingUserId;
    }
}