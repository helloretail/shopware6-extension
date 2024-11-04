<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class HelloRetailClientService
{
    private const url = "https://core.helloretail.com/serve/";
    private const userEndpoint = "trackingUser";
    private ?string $apiKey = null;
    private ?Client $client = null;

    public function __construct(protected SystemConfigService $systemConfigService)
    {
    }

    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client();
        }
        return $this->client;
    }

    public function loadAuthData(): void
    {
        if (!$this->apiKey) {
            $this->apiKey = $this->systemConfigService->get('HelretHelloRetail.config.partnerId') ?? null;
        }
    }

    private function getCookieUserId(): ?string
    {
        //returns cookie, unless user has opted out
        return $_COOKIE['hello_retail_id'] ?? null;
    }

    public function callApi(string $endpoint, Mixed $request = [], string $type = 'page'): array
    {
        $this->loadAuthData();
        $client = $this->getClient();

        if ($type != 'page') {
            $request = $this->formatRequestBody($request, $type);
        }

        $body = json_encode($request);

        try {
            $response = $client->send(new Request(
                'POST',
                self::url . $endpoint,
                ['Content-Type' => 'application/json'],
                $body
            ));
        } catch (GuzzleException $e) {
            dump($e->getMessage());
            return [];
        }

        if ($response->getStatusCode() != "200") {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function formatRequestBody(mixed $request, string $type): array
    {
        $baseBody = [
            "websiteUuid" => $this->apiKey,
            "trackingUserId" => $this->getCookieUserId()
        ];

        if ($type === 'recommendations') {
            $baseBody['requests'] = is_array($request) ? $request : [$request];
        } else {
            $baseBody[] = $request;
        }

        return $baseBody;
    }
}
