<?php declare(strict_types=1);

namespace Helret\HelloRetail\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Helret\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Helret\HelloRetail\HelretHelloRetail;

/**
 * @RouteScope(scopes={"api"})
 */
class ApiController extends AbstractController
{
    /**
     * @var ProfileExporterInterface
     */
    protected $profileExporter;

    /**
     * ApiController constructor.
     * @param ProfileExporterInterface $profileExporter
     */
    public function __construct(ProfileExporterInterface $profileExporter)
    {
        $this->profileExporter = $profileExporter;
    }

    //phpcs:disable
    /**
     * @Route("/api/v{version}/helret/hello-retail/getTypeId", name="api.action.helret.hello-retail.getTypeId", methods={"GET"})
     * @param Context $context
     * @return Response
     */
    //phpcs:enable
    public function getTypeId(Context $context): Response
    {
        return new Response(HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL);
    }

    //phpcs:disable
    /**
     * @Route("/api/v{version}/helret/hello-retail/generateFeed/{salesChannelId}/{feed}", name="api.action.helret.hello-retail.generateFeed", methods={"POST"})
     * @param Context $context
     * @param $salesChannelId
     * @param $feed
     * @return JsonResponse
     */
    //phpcs:enable
    public function generateFeed(Context $context, $salesChannelId, $feed): JsonResponse
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
}
