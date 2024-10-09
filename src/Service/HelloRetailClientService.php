<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Helret\HelloRetail\Service\Models\Requests\PageRequest;
use Helret\HelloRetail\Service\Models\Requests\RecommendationRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class HelloRetailClientService
{
    private const url = "https://core.helloretail.com/serve/";
    private string|null $apiKey = null;
    private Client $client;

    public function __construct(protected SystemConfigService $systemConfigService)
    {
        $this->client = new Client();
        $this->loadAuthData();
    }

    public function loadAuthData(): void
    {
        if (!$this->apiKey) {
            $this->apiKey = $this->systemConfigService->get('HelretHelloRetail.config.partnerId') ?? null;
        }
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    private function getCookieUserId(): ?string
    {
        //returns cookie, unless user has opted out
        return $_COOKIE['hello_retail_id'] ?? null;
    }

    public function callApi(string $endpoint, RecommendationRequest|PageRequest $request): array
    {
        $request->setWebsiteUuid($this->apiKey);
        $request->setTrackingUserId($this->getCookieUserId());

        $body = json_encode($request);
        try {
            $response = $this->client->send(new Request(
                'POST',
                self::url . $endpoint,
                ['Content-Type' => 'application/json'],
                $body
            ));
        } catch (GuzzleException $e) {
            return [];
        }


        if ($response->getStatusCode() != "200") {
            return array($response);
        }
        return json_decode($response->getBody()->getContents(), true);
    }

}