<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Export\SalesChannel;

use Helret\HelloRetail\HelretHelloRetail;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\ProductExport\Exception\ExportNotGeneratedException;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class FileController extends AbstractController
{
    /** @var FilesystemInterface $fileSystem Public fileSystem */
    protected FilesystemInterface $fileSystem;

    public function __construct(
        FilesystemInterface $fileSystem
    ) {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @Since("6.4.14.0")
     * @Route("/hello-retail/{feedDirectory}/{fileName}",
     *     name="store.api.hello-retail.feed.export",
     *     methods={"GET"},
     *     defaults={"auth_required"=false})
     */
    public function index(Request $request): Response
    {
        $path = HelretHelloRetail::STORAGE_PATH . "/{$request->get("feedDirectory")}";

        if (!$this->fileSystem->has("$path/{$request->get("fileName")}")) {
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
}
