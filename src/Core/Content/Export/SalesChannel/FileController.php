<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Export\SalesChannel;

use Helret\HelloRetail\HelretHelloRetail;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Shopware\Core\Content\ProductExport\Exception\ExportNotGeneratedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FileController extends AbstractController
{
    public function __construct(
        protected Filesystem $fileSystem
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
}
