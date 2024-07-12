<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Export\SalesChannel;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Helret\HelloRetail\HelretHelloRetail;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\ProductExport\Exception\ExportNotGeneratedException;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class FileController extends AbstractController
{
    /** @var FilesystemInterface $fileSystem Public fileSystem */
    protected FilesystemInterface $fileSystem;

    public function __construct(
        FilesystemInterface $fileSystem,
        protected Connection $connection,
        protected string $projectRoot
    ) {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @Since("6.4.14.0")
     * @Route("/hello-retail/{feedDirectory}/{fileName}",
     *     name="store.api.hello-retail.feed.export",
     *     methods={"GET"},
     *     defaults={"auth_required"=false, "XmlHttpRequest"=true, "_httpCache"=false})
     */
    public function index(Request $request): Response
    {
        $this->checkAuthorization($request);

        $path = $this->getFeedDirectoryPath() . DIRECTORY_SEPARATOR . "{$request->get("feedDirectory")}";
        $file = $path . DIRECTORY_SEPARATOR . $request->get("fileName");

        if (!file_exists($file)) {
            // Generate
            throw new ExportNotGeneratedException();
        }

        $encoding = "UTF-8";
        $content = file_get_contents($file);
        if ($content === false) {
            throw new \RuntimeException("Failed to read the file content.");
        }
        return (new Response(
            $content ?: null,
            200,
            ['Content-Type' => "text/xml;charset=$encoding"]
        ))->setCharset($encoding);
    }

    protected function getFeedDirectoryPath(): string
    {
        $filesDir = DIRECTORY_SEPARATOR . $this->projectRoot . DIRECTORY_SEPARATOR . "files";
        return $filesDir . DIRECTORY_SEPARATOR . HelretHelloRetail::STORAGE_PATH;
    }

    /**
     * @throws Exception
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
