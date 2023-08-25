<?php declare(strict_types=1);

namespace Helret\HelloRetail\Controller;

use Helret\HelloRetail\Service\ExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Helret\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Helret\HelloRetail\HelretHelloRetail;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
class ApiController extends AbstractController
{
    public function __construct(
        protected ProfileExporterInterface $profileExporter,
        protected ExportService $exportService,
        protected DecoderInterface $serializer
    ) {}

    #[Route(
        path: '/api/helret/hello-retail/getTypeId',
        name: 'api.action.helret.hello-retail.getTypeId',
        methods: ['GET']
    )]
    public function getTypeId(): Response
    {
        return new Response(HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL);
    }

    #[Route(
        path: '/api/helret/hello-retail/generateFeed/{salesChannelId}/{feed}',
        name: 'api.action.helret.hello-retail.generateFeed',
        methods: ['POST']
    )]
    public function generateFeed(string $salesChannelId, string $feed): JsonResponse
    {
        try {
            if (in_array($feed, $this->profileExporter->generate($salesChannelId, [$feed]))) {
                throw new \Exception("$feed could not be exported");
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Export for ' . $feed . 's has been queued'
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }

    #[Route(
        path: '/api/helret/hello-retail/getExportEntities',
        name: 'api.action.helret.hello-retail.getExportEntities',
        methods: ['GET']
    )]
    public function getExportEntities(): JsonResponse
    {
        $feeds = [];
        foreach ($this->exportService->getFeeds() as $key => $feed) {
            $feeds[$key] = $this->serializer->normalize($feed);
        }

        return new JsonResponse([
            "feeds" => $feeds
        ]);
    }
}
