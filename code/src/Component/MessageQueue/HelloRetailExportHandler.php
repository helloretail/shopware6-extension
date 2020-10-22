<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Component\MessageQueue;

use Shopware\Production\Kernel;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Wexo\HelloRetail\Export\ExportEntityElement;
use Wexo\HelloRetail\Export\TemplateType;
use Wexo\HelloRetail\Service\HelloRetailService;
use Wexo\HelloRetail\WexoHelloRetail;

/**
 * Class HelloRetailExportHandler
 * @package Wexo\HelloRetail\Component\MessageQueue
 */
class HelloRetailExportHandler extends AbstractMessageHandler
{
    // TODO: Should be settings
    private const RETRIES = 20;
    private const SLEEP_BETWEEN_RETRIES = 20; // Seconds

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Translator
     */
    protected $translator;
    /**
     * @var Filesystem|FilesystemInterface
     */
    protected $filesystem;
    /**
     * @var HelloRetailService
     */
    protected $helloRetailService;
    /**
     * @var MessageBusInterface
     */
    protected $bus;

    /**
     * HelloRetailExportHandler constructor.
     *
     * @param LoggerInterface                                        $logger
     * @param ContainerInterface                                     $container
     * @param Translator                                             $translator
     * @param HelloRetailService                                     $helloRetailService
     * @param MessageBusInterface                                    $bus
     * @param SystemConfigService $configService
     * @param Kernel $kernel
     */
    public function __construct(
        LoggerInterface $logger,
        ContainerInterface $container,
        Translator $translator,
        HelloRetailService $helloRetailService,
        MessageBusInterface $bus,
        SystemConfigService $configService,
        Kernel $kernel
    ) {
        $this->logger = $logger;
        $this->container = $container;
        $this->translator = $translator;
        $this->helloRetailService = $helloRetailService;
        $this->bus = $bus;

        $storagePath = $configService->get('WexoHelloRetail.config.storagepath') ?? 'helloretail';
        $projectDir = $kernel->getProjectDir();
        $publicDir = $projectDir . '/public';
        $fullPath = $publicDir . '/' . $storagePath;
        $localFilesystemAdapter = new Local($fullPath);
        $this->filesystem = new Filesystem($localFilesystemAdapter);

    }

    public static function getHandledMessages(): iterable
    {
        return [ExportEntityElement::class];
    }

    /**
     * @param ExportEntityElement $message
     */
    public function handle($message): void
    {
        if ($message->getTemplateType() === TemplateType::FOOTER) {
            $this->collectFiles($message);
            return;
        }
        $feedEntity = $message->getFeedEntity();
        $feed = $feedEntity->getFeed();
        $salesChannelContext = $message->getSalesChannelContext();

        $this->translator->injectSettings(
            $salesChannelContext->getSalesChannel()->getId(),
            $feedEntity->getDomain()->getLanguageId(),
            $feedEntity->getDomain()->getLanguage()->getLocaleId(),
            $salesChannelContext->getContext()
        );

        /** @var EntityRepositoryInterface $repository */
        $repository = $this->container->get("$feed.repository");

        $criteria = new Criteria([$message->getId()]);
        foreach ($feedEntity->getAssociations() as $association) {
            $criteria->addAssociation($association);
        }

        $entity = $repository->search($criteria, $salesChannelContext->getContext())->first();

        try {
            $output = $this->helloRetailService->renderBody(
                $feedEntity,
                $salesChannelContext,
                [
                    "{$feed}" => $entity
                ]
            );
            if (!$output) {
                $this->translator->resetInjection();
                $this->helloRetailService->exportLogger(
                    WexoHelloRetail::EXPORT_ERROR,
                    [
                        'entityId' => $entity->getId(),
                        'feed' => $feed,
                        'entityType' => $message->getEntityType(),
                        'templateType' => $message->getTemplateType()
                    ]
                );
                return;
            }
            $this->filesystem->put($message->getDirectory() . DIRECTORY_SEPARATOR . $message->getId(), $output);
            $this->translator->resetInjection();
        } catch (\Error | \TypeError | \Exception $e) {
            $this->helloRetailService->exportLogger(
                WexoHelloRetail::EXPORT_ERROR,
                [
                    'entityId' => $entity->getId(),
                    'feed' => $feed,
                    'entityType' => $message->getEntityType(),
                    'templateType' => $message->getTemplateType(),
                    'error' => $e->getMessage(),
                    'errorTrace' => $e->getTraceAsString(),
                    'errorType' => get_class($e)
                ]
            );
        }
    }

    private function collectFiles(ExportEntityElement $message)
    {
        $dir = $message->getDirectory();

        $files = [];
        foreach ($this->filesystem->listContents($dir) as $file) {
            $filename = $file['filename'] ?? null;
            if ($filename == TemplateType::HEADER) {
                // Insert at the beginning of the array
                array_unshift($files, $filename);
                continue;
            }
            $files[] = $filename;
        }

        $allIds = $message->getAllIds();
        $successes = array_intersect($allIds, $files);
        $failures = 0;
        // TODO: Threshold should be a setting
        $successThreshold = floor((count($allIds) * 0.90));

        if (count($successes) >= $successThreshold) {
            $feedContent = "";
            try {
                $header = $dir . DIRECTORY_SEPARATOR . array_splice($files, 0, 1)[0];
                $feedContent .= $this->filesystem->read($header);
            } catch (\Error | \TypeError | FileNotFoundException | \Exception $e) {
                $this->helloRetailService->exportLogger(
                    WexoHelloRetail::EXPORT_ERROR,
                    [
                        'header' => $header ?? null,
                        'feed' => $message->getFeedEntity()->getFeed(),
                        'entityType' => $message->getEntityType(),
                        'templateType' => $message->getTemplateType(),
                        'error' => $e->getMessage(),
                        'errorTrace' => $e->getTraceAsString(),
                        'errorType' => get_class($e)
                    ]
                );
                return;
            }
            foreach ($files as $file) {
                try {
                    $feedContent .= $this->filesystem->read($dir . DIRECTORY_SEPARATOR . $file);
                } catch (FileNotFoundException $e) {
                    $failures++;
                    continue;
                }
            }
            // Construct file
            $feedContent .= $this->helloRetailService->renderFooter(
                $message->getFeedEntity(),
                $message->getSalesChannelContext()
            );

            if ($failures > (count($allIds) - $successThreshold)) {
                $this->handleRetry(
                    $message,
                    $failures,
                    $successThreshold ?? null,
                    $allIds,
                    $dir
                );

                return;
            } else {
                $this->filesystem->put($message->getFeedEntity()->getFile(), $feedContent);
            }
        } else {
            $this->handleRetry(
                $message,
                $failures,
                $successThreshold  ?? null,
                $allIds,
                $dir
            );

            return;
        }

        $this->helloRetailService->exportLogger(
            WexoHelloRetail::EXPORT_SUCCESS,
            [
                'feed' => $message->getFeedEntity()->getFeed()
            ],
            Logger::INFO
        );

        // TODO: Bug in normalizeRelativePath() which removes trailing slash, meaning the folder will not be deleted
        // @see vendor/league/flysystem/src/Util.php
        $this->filesystem->deleteDir($dir);
    }

    /**
     * @param ExportEntityElement $message
     * @param int $failures
     * @param float|null $successThreshold
     * @param array|null $allIds
     * @param string $dir
     */
    private function handleRetry(
        ExportEntityElement $message,
        int $failures,
        ?float $successThreshold,
        ?array $allIds,
        string $dir
    ) {
        $retryCount = $message->getRetryCount();
        if ($retryCount < self::RETRIES) {
            sleep(self::SLEEP_BETWEEN_RETRIES);
            $message->setRetryCount($retryCount + 1);
            $this->bus->dispatch(new Envelope($message));
        } else {
            $this->helloRetailService->exportLogger(
                WexoHelloRetail::EXPORT_ERROR,
                [
                    'retryCount' => $retryCount,
                    'failures' => $failures,
                    'allIdsCount' => count($allIds),
                    'successThreshold' => $successThreshold,
                    'feed' => $message->getFeedEntity()->getFeed(),
                    'entityType' => $message->getEntityType(),
                    'templateType' => $message->getTemplateType()
                ]
            );

            $this->filesystem->deleteDir($dir);
        }
    }
}
