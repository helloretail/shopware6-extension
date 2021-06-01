<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use Error;
use Exception;
use TypeError;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FilesystemInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Adapter\Twig\Exception\StringTemplateRenderingException;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Kernel;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Helret\HelloRetail\Export\EntityType;
use Helret\HelloRetail\Export\ExportEntityElement;
use Helret\HelloRetail\Export\ExportEntityInterface;
use Helret\HelloRetail\Export\FeedEntity;
use Helret\HelloRetail\Export\FeedEntityInterface;
use Helret\HelloRetail\Export\TemplateType;
use Helret\HelloRetail\HelretHelloRetail;

/**
 * Class HelloRetailService
 * @package Helret\HelloRetail\Service
 */
class HelloRetailService
{
    protected EntityRepositoryInterface $logEntryRepository;
    protected LoggerInterface $logger;
    protected MessageBusInterface $bus;
    protected StringTemplateRenderer $templateRenderer;
    protected ContainerInterface $container;
    protected SalesChannelContextServiceInterface $salesChannelContextService;
    protected SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler;
    protected SerializerInterface $serializer;
    protected EntityRepositoryInterface $salesChannelDomainRepository;
    protected FilesystemInterface $filesystem;
    protected SystemConfigService $configService;
    protected Kernel $kernel;

    /**
     * HelloRetailService constructor.
     *
     * @param EntityRepositoryInterface $logEntryRepository
     * @param LoggerInterface $logger
     * @param MessageBusInterface $bus
     * @param StringTemplateRenderer $templateRenderer
     * @param ContainerInterface $container
     * @param SalesChannelContextServiceInterface $salesChannelContextService
     * @param SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler
     * @param SerializerInterface $serializer
     * @param EntityRepositoryInterface $salesChannelDomainRepository
     * @param SystemConfigService $configService
     * @param Kernel $kernel
     */
    public function __construct(
        EntityRepositoryInterface $logEntryRepository,
        LoggerInterface $logger,
        MessageBusInterface $bus,
        StringTemplateRenderer $templateRenderer,
        ContainerInterface $container,
        SalesChannelContextServiceInterface $salesChannelContextService,
        SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
        SerializerInterface $serializer,
        EntityRepositoryInterface $salesChannelDomainRepository,
        SystemConfigService $configService,
        Kernel $kernel
    ) {
        $this->logEntryRepository = $logEntryRepository;
        $this->logger = $logger;
        $this->bus = $bus;
        $this->templateRenderer = $templateRenderer;
        $this->container = $container;
        $this->salesChannelContextService = $salesChannelContextService;
        $this->seoUrlPlaceholderHandler = $seoUrlPlaceholderHandler;
        $this->serializer = $serializer;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->configService = $configService;
        $this->kernel = $kernel;

        $fullPath = $this->getFeedDirectoryPath();
        $localFilesystemAdapter = new Local($fullPath);
        $this->filesystem = new Filesystem($localFilesystemAdapter);
    }

    /**
     * @return string
     */
    public function getFeedDirectoryPath(): string
    {
        $projectDir = $this->kernel->getProjectDir();
        $publicDir = $projectDir . DIRECTORY_SEPARATOR . 'public';

        return $publicDir . DIRECTORY_SEPARATOR . HelretHelloRetail::STORAGE_PATH;
    }

    /**
     * @param ExportEntityInterface $exportEntity
     * @param string $feed
     * @return bool
     */
    public function export(ExportEntityInterface $exportEntity, string $feed): bool
    {
        $salesChannelDomainCriteria = new Criteria([$exportEntity->getSalesChannelDomainId()]);
        $salesChannelDomainCriteria->addAssociation('language');

        /** @var SalesChannelDomainEntity $salesChannelDomain */
        $salesChannelDomain = $this->salesChannelDomainRepository
            ->search($salesChannelDomainCriteria, Context::createDefaultContext())->first();

        /*
         * No token needed since we haven't generated one with any settings.
         * Implement when we need to pass currency.
         * @see vendor/shopware/core/Content/ProductExport/ScheduledTask/ProductExportPartialGenerationHandler.php
         * finalizeExport()
         */
        $salesChannelContext = $this->salesChannelContextService->get(
            $exportEntity->getStorefrontSalesChannelId(),
            "",
            $salesChannelDomain->getLanguageId()
        );

        /** @var FeedEntityInterface $feedEntity */
        try {
            $feedEntity = $this->serializer
                ->deserialize(json_encode($exportEntity->getFeeds()[$feed]), FeedEntity::class, 'json');

            $feedEntity->setFeedDirectory($exportEntity->getFeedDirectory());
            $feedEntity->setFeed($feed);
            $feedEntity->setDomain($salesChannelDomain);
        } catch (Error | TypeError | NotEncodableValueException | Exception $e) {
            $this->exportLogger(
                HelretHelloRetail::EXPORT_ERROR,
                [
                    'feed' => $feed,
                    'error' => $e->getMessage(),
                    'errorTrace' => $e->getTraceAsString(),
                    'errorType' => get_class($e)
                ]
            );

            return false;
        }

        /** @var EntityRepositoryInterface $repository */
        $repository = $this->container->get("$feed.repository");

        $criteria = new Criteria();
        if (EntityType::getMatchingEntityType($feed) == EntityType::PRODUCT) {
            $criteria->addFilter(new EqualsFilter('product.active', true));
        }

        $entityIdsResult = $repository->searchIds($criteria, $salesChannelContext->getContext());
        $entityIds = $entityIdsResult->getIds();

        $content = $this->renderHeader($feedEntity, $salesChannelContext, [
            "{$feed}sTotal" => $entityIdsResult->getTotal()
        ]);

        // Create temp dir for all file parts
        $tmpDir = 'hello-retail-generation-content/'
            . Uuid::randomHex()
            . HelretHelloRetail::FILE_TYPE_INDICATOR_SEPARATOR
            . $feed;

        $this->filesystem->put($tmpDir . DIRECTORY_SEPARATOR . TemplateType::HEADER, $content);

        foreach ($entityIds as $entityId) {
            $this->bus->dispatch(
                new Envelope(
                    new ExportEntityElement(
                        $salesChannelContext,
                        $tmpDir,
                        $entityId,
                        $feedEntity,
                        EntityType::getMatchingEntityType($feed),
                        TemplateType::BODY
                    )
                )
            );
        }

        $footerElement = new ExportEntityElement(
            $salesChannelContext,
            $tmpDir,
            TemplateType::FOOTER,
            $feedEntity,
            EntityType::getMatchingEntityType($feed),
            TemplateType::FOOTER
        );
        $footerElement->setAllIds($entityIds);

        $this->bus->dispatch(new Envelope($footerElement));

        return true;
    }

    /**
     * @param FeedEntityInterface $feedEntity
     * @param SalesChannelContext $context
     * @param array $data
     * @return bool|string
     */
    public function renderHeader(FeedEntityInterface $feedEntity, SalesChannelContext $context, array $data = [])
    {
        return $this->renderTemplate($feedEntity->getHeaderTemplate(), $data, $context);
    }

    /**
     * @param FeedEntityInterface $feedEntity
     * @param SalesChannelContext $context
     * @param array $data
     * @return string
     */
    public function renderBody(
        FeedEntityInterface $feedEntity,
        SalesChannelContext $context,
        array $data = []
    ): string {
        return $this->replaceSeoUrlPlaceholder(
            $this->renderTemplate($feedEntity->getBodyTemplate(), $data, $context),
            $feedEntity->getDomain(),
            $context
        );
    }

    /**
     * @param FeedEntityInterface $feedEntity
     * @param SalesChannelContext $context
     * @param array $data
     * @return bool|string
     */
    public function renderFooter(FeedEntityInterface $feedEntity, SalesChannelContext $context, $data = [])
    {
        return $this->renderTemplate($feedEntity->getFooterTemplate(), $data, $context);
    }

    /**
     * @param string $content
     * @param SalesChannelDomainEntity $domain
     * @param SalesChannelContext $salesChannelContext
     * @return string
     */
    public function replaceSeoUrlPlaceholder(
        string $content,
        SalesChannelDomainEntity $domain,
        SalesChannelContext $salesChannelContext
    ): string {
        return $this->seoUrlPlaceholderHandler->replace($content, $domain->getUrl(), $salesChannelContext);
    }

    /**
     * @param string $event
     * @param array $context
     * @param int $level
     */
    public function exportLogger(
        string $event,
        array $context,
        int $level = Logger::ERROR
    ) {
        $this->logEntryRepository->create(
            [
                [
                    'message' => $event,
                    'context' => $context,
                    'level' => $level,
                    'channel' => HelretHelloRetail::LOG_CHANNEL
                ]
            ],
            Context::createDefaultContext()
        );
    }

    /**
     * @param string|null $template
     * @param array $data
     * @param SalesChannelContext $context
     * @return bool|string
     */
    private function renderTemplate(?string $template, array $data, SalesChannelContext $context)
    {
        try {
            return $this->templateRenderer->render(
                    $template,
                    $data,
                    $context->getContext()
                ) . PHP_EOL;
        } catch (Error | TypeError | Exception | StringTemplateRenderingException $e) {
            $this->exportLogger(
                HelretHelloRetail::EXPORT_ERROR,
                [
                    'template' => $template,
                    'data' => $data,
                    'error' => $e->getMessage(),
                    'errorTrace' => $e->getTraceAsString(),
                    'errorType' => get_class($e)
                ]
            );

            return false;
        }
    }
}
