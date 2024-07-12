<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service;

use DateTime;
use Error;
use Exception;
use Helret\HelloRetail\Core\Content\Feeds\ExportEntity;
use Shopware\Core\Framework\Adapter\Twig\TwigVariableParser;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
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
    protected string $projectRoot;
    protected TwigVariableParser $twigVariableParser;
    protected ExportService $exportService;

    /**
     * HelloRetailService constructor.
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
        string $projectRoot,
        TwigVariableParser $twigVariableParser,
        ExportService $exportService
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
        $this->projectRoot = $projectRoot;
        $this->twigVariableParser = $twigVariableParser;
        $this->exportService = $exportService;

        $fullPath = $this->getFeedDirectoryPath();
        $localFilesystemAdapter = new Local($fullPath);
        $this->filesystem = new Filesystem($localFilesystemAdapter);
    }

    /**
     * @return string
     */
    public function getFeedDirectoryPath(): string
    {
        $publicDir = $this->projectRoot . DIRECTORY_SEPARATOR . 'files';
        return $publicDir . DIRECTORY_SEPARATOR . HelretHelloRetail::STORAGE_PATH;
    }

    /**
     * @param ExportEntityInterface $exportEntity
     * @param string $feed
     * @return bool
     * @throws Exception
     */
    public function export(ExportEntityInterface $exportEntity, string $feed): bool
    {
        $salesChannelDomainCriteria = new Criteria([$exportEntity->getSalesChannelDomainId()]);
        $salesChannelDomainCriteria->addAssociation('language');

        /** @var SalesChannelDomainEntity $salesChannelDomain */
        $salesChannelDomain = $this->salesChannelDomainRepository
            ->search($salesChannelDomainCriteria, Context::createDefaultContext())->first();

        /**
         * No token needed since we haven't generated one with any settings.
         * Implement when we need to pass currency.
         * @see ProductExportPartialGenerationHandler::finalizeExport
         */
        $salesChannelContext = $this->salesChannelContextService->get(new SalesChannelContextServiceParameters(
            $exportEntity->getStorefrontSalesChannelId(),
            "",
            $salesChannelDomain->getLanguageId()
        ));

        /** @var FeedEntityInterface $feedEntity */
        if (isset($exportEntity->getFeeds()[$feed])) {
            try {
                $feedEntity = $this->serializer
                    ->deserialize(json_encode($exportEntity->getFeeds()[$feed]), FeedEntity::class, 'json');
            } catch (Error|TypeError|NotEncodableValueException|Exception $e) {
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
        } else {
            $feedEntity = new FeedEntity();
        }

        $exportFeed = $this->exportService->getFeed($feed);

        $feedEntity->setFeedDirectory($exportEntity->getFeedDirectory());
        $feedEntity->setFeed($feed);
        $feedEntity->setDomain($salesChannelDomain);
        $feedEntity->setEntity($exportFeed->getEntity());
        $feedEntity->setFile($exportFeed->getFile());

        $this->setInheritedTemplates($feedEntity, $exportFeed);
        if (!$feedEntity->getHeaderTemplate() || !$feedEntity->getBodyTemplate() || !$feedEntity->getFooterTemplate()) {
            return false;
        }


        $criteria = new Criteria();
        if (EntityType::getMatchingEntityType($feed) == EntityType::PRODUCT) {
            $criteria->addFilter(new EqualsFilter('product.active', true));
            $criteria->addFilter(new EqualsFilter(
                'product.visibilities.salesChannelId',
                $salesChannelContext->getSalesChannelId()
            ));
        } elseif (EntityType::getMatchingEntityType($feed) == EntityType::CATEGORY) {
            $categoryIds = [
                $salesChannelContext->getSalesChannel()->getNavigationCategoryId(),
            ];

            if ($salesChannelContext->getSalesChannel()->getServiceCategoryId()) {
                $categoryIds[] = $salesChannelContext->getSalesChannel()->getServiceCategoryId();
            }
            if ($salesChannelContext->getSalesChannel()->getFooterCategoryId()) {
                $categoryIds[] = $salesChannelContext->getSalesChannel()->getFooterCategoryId();
            }

            /**
             * Categories by salesChannel category ids.
             * @see CategoryBreadcrumbBuilder::getSalesChannelFilter
             */
            $criteria->addFilter(new OrFilter(array_map(
                fn(string $id) => new ContainsFilter('path', '|' . $id . '|'),
                $categoryIds
            )));
            $criteria->addFilter(new EqualsFilter('category.active', true));
        } elseif (EntityType::getMatchingEntityType($feed) == EntityType::ORDER) {
            $salesChannelId = $salesChannelContext->getSalesChannelId();
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
            if ($this->configService->get('HelretHelloRetail.config.orderLimit', $salesChannelId)) {
                $amountOfMonths = $this->configService->getInt(
                    'HelretHelloRetail.config.orderLimitMonths',
                    $salesChannelId
                ) ?: 2;
                $criteria->addFilter(new RangeFilter(
                    'createdAt',
                    [RangeFilter::GTE => (new DateTime("-{$amountOfMonths} month"))->format('Y-m-d')]
                ));
            }
        }

        $this->extendCriteria($criteria, $feed, $feedEntity, $salesChannelContext);

        $repository = $this->container->get(("{$exportFeed->getEntity()}.repository"));
        if ($repository instanceof SalesChannelRepositoryInterface) {
            $entityIdsResult = $repository->searchIds($criteria, $salesChannelContext);
            /** @var EntityRepositoryInterface $pureRepo */
            $pureRepo = $this->container->get(("$feed.repository"));
            $associations = $this->getAssociations($feedEntity->getBodyTemplate(), $pureRepo);
            unset($pureRepo);
        } else {
            /** @var EntityRepositoryInterface $repository */
            $entityIdsResult = $repository->searchIds($criteria, $salesChannelContext->getContext());
            $associations = $this->getAssociations($feedEntity->getBodyTemplate(), $repository);
        }

        // Dynamically add associations
        $feedEntity->setAssociations(array_merge(
            $feedEntity->getAssociations(),
            $exportFeed->associations,
            $associations
        ));

        $entityIds = $entityIdsResult->getIds();

        $content = $this->renderHeader($feedEntity, $salesChannelContext, [
             "{$feed}sTotal" => $entityIdsResult->getTotal(),
             "total" => $entityIdsResult->getTotal(),
             "updatedAt" => date("Y-m-d H:i:s")
        ]);
        if (!$content) {
            // If the header render failed, no need to continue.
            // The error will already have been logged in the renderTemplate function
            return false;
        }

        // Create temp dir for all file parts: {dir}/{salesChannelId}_{entityType}
        // Change: Use same dir (salesChannelId) to ensure lots of folders aren't created in case of failure / staling
        $tmpDir = 'hello-retail-generation-content/'
            . $salesChannelContext->getSalesChannelId()
            . HelretHelloRetail::FILE_TYPE_INDICATOR_SEPARATOR
            . $feed;

        $this->filesystem->put($tmpDir . DIRECTORY_SEPARATOR . TemplateType::HEADER, $content);

        $config = $this->configService->get("HelretHelloRetail.config", $salesChannelContext->getSalesChannelId());

        foreach ($entityIds as $entityId) {
            $message = new ExportEntityElement(
                $salesChannelContext,
                $tmpDir,
                $entityId,
                $feedEntity,
                EntityType::getMatchingEntityType($feed),
                TemplateType::BODY
            );
            $message->setExportConfig($config);

            $this->bus->dispatch(new Envelope($message));
        }

        $footerElement = new ExportEntityElement(
            $salesChannelContext,
            $tmpDir,
            TemplateType::FOOTER,
            $feedEntity,
            EntityType::getMatchingEntityType($feed),
            TemplateType::FOOTER
        );
        $footerElement->setExportConfig($config);
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
    ): void {
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
            return $this->templateRenderer->render($template, $data, $context->getContext()) . PHP_EOL;
        } catch (Error|TypeError|Exception|StringTemplateRenderingException $e) {
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
        }
        return false;
    }

    protected function getAssociations(string $template, EntityRepositoryInterface $repo): array
    {
        try {
            $variables = $this->twigVariableParser->parse($template);
        } catch (\Exception $e) {
            return [];
            // Should we throw, or just rely on the associations from the conf file?
            // throw new RenderProductException($e->getMessage());
        }

        $associations = [];
        foreach ($variables as $variable) {
            $associations[] = EntityDefinitionQueryHelper::getAssociationPath($variable, $repo->getDefinition());
        }

        return array_filter(array_unique($associations));
    }

    protected function setInheritedTemplates(FeedEntityInterface $feedEntity, ?ExportEntity $exportEntity): void
    {
        if ($feedEntity->getHeaderTemplate() && $feedEntity->getBodyTemplate() && $feedEntity->getFooterTemplate()) {
            return;
        }

        if (!$exportEntity) {
            return;
        }

        if (!$feedEntity->getHeaderTemplate()) {
            $feedEntity->setHeaderTemplate($exportEntity->getHeaderTemplate());
        }
        if (!$feedEntity->getBodyTemplate()) {
            $feedEntity->setBodyTemplate($exportEntity->getBodyTemplate());
        }
        if (!$feedEntity->getFooterTemplate()) {
            $feedEntity->setFooterTemplate($exportEntity->getFooterTemplate());
        }
    }

    protected function extendCriteria(
        Criteria $criteria,
        string $feed,
        FeedEntityInterface $feedEntity,
        SalesChannelContext $context
    ): void {
    }
}
