<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wexo\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Wexo\HelloRetail\WexoHelloRetail;

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
     * @Route("/api/v{version}/wexo/hello-retail/getTypeId", name="api.action.wexo.hello-retail.getTypeId", methods={"GET"})
     * @param Context $context
     * @return Response
     */
    //phpcs:enable
    public function getTypeId(Context $context): Response
    {
        return new Response(WexoHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL);
    }

    //phpcs:disable
    /**
     * @Route("/api/v{version}/wexo/hello-retail/generateFeed/{salesChannelId}/{feed}", name="api.action.wexo.hello-retail.generateFeed", methods={"POST"})
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
                'message' => 'Export has been queued'
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
