<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
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

    public function getApiKey(): string|null
    {
        return $this->apiKey;
    }

    public function callApi(Mixed $request, string $endpoint): array
    {
        if (!is_array($request)) {
            $request = [$request];
        }
        $body = json_encode([
            "websiteUuid" => $this->apiKey,
            "requests" => $request
        ]);
        $response = $this->client->send(new Request(
            'POST',
            self::url . $endpoint,
            ['Content-Type' => 'application/json'],
            $body
        ));

        return json_decode($response->getBody()->getContents(), true);
    }

}