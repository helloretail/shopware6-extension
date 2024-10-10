<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models\Requests;

use Helret\HelloRetail\Service\Models\Recommendation;

class RecommendationRequest extends Request
{
    public array $requests;

    public function __construct(array $requests, ?string $websiteUuid = null, ?string $trackingUserId = null)
    {
        $this->requests = $requests;
        $this->websiteUuid = $websiteUuid;
        $this->trackingUserId = $trackingUserId;

        parent::__construct($websiteUuid, $trackingUserId);
    }

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function setRequests(array $requests): void
    {
        $this->requests = $requests;
    }
}