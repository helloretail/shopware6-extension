<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class HelloRetailClientService
{
    private Logger $logger;
    private const URL = "https://core.helloretail.com/serve/";
    private ?Client $client = null;

    public function __construct(protected SystemConfigService $systemConfigService, public string $logDir)
    {
        $this->logger = new Logger('hello-retail');
        $this->logger->pushHandler(new StreamHandler($logDir . '/hello-retail.log', \Monolog\Level::Error));
    }

    public function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client();
        }

        return $this->client;
    }

    private function getCookieUserId(): ?string
    {
        //returns cookie, unless user has opted out
        return $_COOKIE['hello_retail_id'] ?? null;
    }

    public function createRequest(
        string $endpoint,
        array $request = [],
        string $type = 'page',
        ?string $salesChannelId = null
    ): Request {
        if ($type != 'page') {
            $request = $this->formatRequestBody($request, $type, $salesChannelId);
        }

        $body = json_encode($request);
        return new Request(
            'POST',
            self::URL . $endpoint,
            ['Content-Type' => 'application/json'],
            $body
        );
    }

    public function callApi(
        string $endpoint,
        array $request = [],
        string $type = 'page',
        ?string $salesChannelId = null
    ): array {
        $client = $this->getClient();

        // Ensure that we always can access product.id
        if (isset($request['products']['fields']) && !isset($request['products']['fields']['extraData.id'])) {
            $request['products']['fields'][] = 'extraData.id';
        }

        try {
            $response = $client->send(
                $this->createRequest(
                    $endpoint,
                    $request,
                    $type,
                    $salesChannelId
                )
            );
        } catch (GuzzleException $e) {
            $this->logger->error('Request failed', [
                'endpoint' => $endpoint,
                'body' => $request,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function formatRequestBody(mixed $request, string $type, ?string $salesChannelId = null): array
    {
        $baseBody = [
            "websiteUuid" => $this->systemConfigService->get('HelretHelloRetail.config.partnerId', $salesChannelId),
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
