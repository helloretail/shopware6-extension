<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Export\SalesChannel;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Helret\HelloRetail\HelretHelloRetail;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Shopware\Core\Content\ProductExport\Exception\ExportNotGeneratedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Uuid\Uuid;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FileController extends AbstractController
{
    public function __construct(
        protected Filesystem $fileSystem,
        protected Connection $connection
    ) {
    }

    /**
     * @throws FilesystemException
     */
    #[Route(
        path: "/hello-retail/{feedDirectory}/{fileName}",
        name: "store.api.hello-retail.feed.export",
        defaults: ['auth_required' => false],
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        $this->checkAuthorization($request);

        $path = HelretHelloRetail::STORAGE_PATH . "/{$request->get("feedDirectory")}";

        if (!$this->fileSystem->fileExists("$path/{$request->get("fileName")}")) {
            // Generate
            throw new ExportNotGeneratedException();
        }

        $encoding = "UTF-8";

        $content = $this->fileSystem->read("$path/{$request->get("fileName")}");
        return (new Response(
            $content ?: null,
            200,
            ['Content-Type' => "text/xml;charset=$encoding"]
        ))->setCharset($encoding);
    }

    /**
     * @throws UnauthorizedHttpException
     */
    protected function checkAuthorization(Request $request): void
    {
        $feedDirectory = $request->get("feedDirectory");
        $expectedToken = $this->getAuthToken($feedDirectory);

        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new UnauthorizedHttpException('Bearer', 'Missing or invalid Authorization header.');
        }

        $token = substr($authHeader, 7);

        if ($token !== $expectedToken) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid token.');
        }
    }


    /**
     * @throws Exception
     */
    protected function getAuthToken(string $feedDirectory): ?string
    {
        if (!$feedDirectory) {
            return null;
        }

        $salesChannelsConfigurations = $this->connection->fetchAllAssociative(<<<SQL
            SELECT
                configuration
            FROM
                `sales_channel`
            WHERE
                sales_channel.type_id = :salesChannelTypeId;
        SQL, [
            'salesChannelTypeId' => Uuid::fromHexToBytes(HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL)
        ]);

        foreach ($salesChannelsConfigurations as $config) {
            $configuration = json_decode($config['configuration'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($configuration['feedDirectory']) && $configuration['feedDirectory'] === $feedDirectory) {
                    return $configuration['authToken'] ?? null;
                }
            }
        }
        return null;
    }
}
