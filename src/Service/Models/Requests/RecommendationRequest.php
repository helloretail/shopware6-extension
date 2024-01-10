<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models\Requests;

use Helret\HelloRetail\Service\Models\Recommendation;

class RecommendationRequest extends Request
{
    public array $requests;

    /**
     * @param array $requests
     * @param null $websiteUuid
     * @param null $trackingUserId
     */
    public function __construct(array $requests, $websiteUuid = null, $trackingUserId = null)
    {
        $this->requests = $requests;

        parent::__construct($websiteUuid, $trackingUserId);
    }

    /**
     * @return array
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @param array $requests
     */
    public function setRequests(array $requests): void
    {
        $this->requests = $requests;
    }
}