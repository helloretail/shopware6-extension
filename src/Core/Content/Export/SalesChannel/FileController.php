<?php declare(strict_types=1);

namespace Helret\HelloRetail\Core\Content\Export\SalesChannel;

use Helret\HelloRetail\HelretHelloRetail;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\ProductExport\Exception\ExportNotGeneratedException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class FileController extends AbstractController
{
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
    public function index(Request $request, Context $context): Response
    {
        $path = HelretHelloRetail::STORAGE_PATH . "/{$request->get("feedDirectory")}";

        if (!$this->fileSystem->has("$path/{$request->get("fileName")}")) {
            // Generate
            throw new ExportNotGeneratedException();
        }

//        dd($this->fileSystem->has(HelretHelloRetail::STORAGE_PATH));
//        $criteria = (new Criteria())
//            ->addFilter(new EqualsFilter("configuration.feedDirectory", $request->get("feedKey")));
//
//        dd($criteria);
//        $export = $this->exportRepository->search($criteria, $context)->first();
//        if (!$export) {
//            throw new ExportNotFoundException(null, $request->get("fileName"));
//        }

////        $filePath = $this->getFilePath();
//        if (!$this->fileSystem->has($filePath){
//            // Generate
//        throw new ExportNotGeneratedExpception();
//    }
//
//
//        // vendor/shopware/core/Content/ProductExport/SalesChannel/ExportController.php
//        $content = $this->fileSystem->read($filePath);
        $contentType = "text/xml"; //$this->getContentType($productExport->getFileFormat());
        $encoding = "UTF-8"; //$productExport->getEncoding();

        $content = $this->fileSystem->read("$path/{$request->get("fileName")}");
        return (new Response(
            $content ? $content : null,
            200,
            ['Content-Type' => $contentType . ';charset=' . $encoding]
        ))->setCharset($encoding);
    }
}
