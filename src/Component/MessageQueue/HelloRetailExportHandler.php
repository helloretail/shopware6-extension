<?php

namespace Wexo\HelloRetail\Component\MessageQueue;

use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\ProductExport\Exception\EmptyExportException;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\Adapter\Twig\Exception\StringTemplateRenderingException;
use Shopware\Core\Framework\Adapter\Twig\Extension\SeoUrlFunctionExtension;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Wexo\HelloRetail\Export\FeedEntity;
use Wexo\HelloRetail\Export\FeedEntityInterface;

class HelloRetailExportHandler extends AbstractMessageHandler
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var StringTemplateRenderer
     */
    protected $templateRenderer;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var SalesChannelContextService
     */
    protected $salesChannelContextService;
    /**
     * @var SeoUrlFunctionExtension
     */
    protected $seoUrlPlaceholderHandler;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var EntityRepositoryInterface
     */
    protected $salesChannelDomainRepository;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * ProfileExporter constructor.
     * @param LoggerInterface $logger
     * @param StringTemplateRenderer $templateRenderer
     * @param ContainerInterface $container
     * @param SalesChannelContextServiceInterface $salesChannelContextService
     * @param SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler
     * @param SerializerInterface $serializer
     * @param EntityRepositoryInterface $salesChannelDomainRepository
     * @param Translator $translator
     * @param Connection $connection
     * @param FilesystemInterface $filesystem
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        LoggerInterface $logger,
        StringTemplateRenderer $templateRenderer,
        ContainerInterface $container,
        SalesChannelContextServiceInterface $salesChannelContextService,
        SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
        SerializerInterface $serializer,
        EntityRepositoryInterface $salesChannelDomainRepository,
        Translator $translator,
        Connection $connection,
        FilesystemInterface $filesystem,
        MessageBusInterface $messageBus
    ) {
        $this->logger = $logger;
        $this->templateRenderer = $templateRenderer;
        $this->container = $container;
        $this->salesChannelContextService = $salesChannelContextService;
        $this->seoUrlPlaceholderHandler = $seoUrlPlaceholderHandler;
        $this->serializer = $serializer;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->translator = $translator;
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->messageBus = $messageBus;
    }

    public static function getHandledMessages(): iterable
    {
        return [HelloRetailExport::class];
    }

    /**
     * @param HelloRetailExport $message
     */
    public function handle($message): void
    {
        if ($this->export($message)) {
            $this->logger->info("Hello Retail {$message->getFeed()} feed succesfully exported");
        } else {
            $this->logger->info("Hello Retail {$message->getFeed()} feed was not exported (check error logs)");
        }
    }

    public function export($message): bool
    {
        $exportEntity = $message->getExportEntity();
        $feed = $message->getFeed();

        $contextToken = Uuid::randomHex();

        $salesChannelDomainCriteria = new Criteria([$exportEntity->getSalesChannelDomainId()]);
        $salesChannelDomainCriteria->addAssociation('language');

        /** @var SalesChannelDomainEntity $salesChannelDomain */
        $salesChannelDomain = $this->salesChannelDomainRepository
            ->search($salesChannelDomainCriteria, Context::createDefaultContext())->first();

        $context = $this->salesChannelContextService->get(
            $exportEntity->getStorefrontSalesChannelId(),
            $contextToken,
            $salesChannelDomain->getLanguageId()
        );

        $this->translator->injectSettings(
            $exportEntity->getStorefrontSalesChannelId(),
            $salesChannelDomain->getLanguageId(),
            $salesChannelDomain->getLanguage()->getLocaleId(),
            $context->getContext()
        );

        /** @var FeedEntityInterface $feedEntity */
        try {
            $feedEntity = $this->serializer
                ->deserialize(json_encode($exportEntity->getFeeds()[$feed]), FeedEntity::class, 'json');
        } catch (NotEncodableValueException $e) {
            $this->logError($feed, $e);

            return false;
        }

        $criteria = new Criteria();

        $criteria->setLimit(100);

        foreach ($feedEntity->getAssociations() as $association) {
            $criteria->addAssociation($association);
        }

        /** @var EntityRepositoryInterface $repository */
        $repository = $this->container->get("$feed.repository");

        $iterator = new RepositoryIterator($repository, $context->getContext(), $criteria);

        $total = $iterator->getTotal();
        if ($total === 0) {
            $this->translator->resetInjection();
            $this->connection->delete('sales_channel_api_context', ['token' => $contextToken]);

            throw new EmptyExportException();
        }

        $parsedEntities = [];

        while ($result = $iterator->fetch()) {
            /** @var EntityRepositoryInterface $entity */
            foreach ($result->getEntities() as $entity) {
                $parsedEntities[] = $entity;
            }
        }

        try {
            $content = $this->templateRenderer->render(
                $feedEntity->getTemplate()['template'],
                [
                    "{$feed}s" => $parsedEntities,
                    "{$feed}sTotal" => count($parsedEntities)
                ],
                $context->getContext()
            );
        } catch (StringTemplateRenderingException $e) {
            $this->logError($feed, $e);

            return false;
        }

        $content = $this->replaceSeoUrlPlaceholder($content, $context);

        $this->translator->resetInjection();
        $this->connection->delete('sales_channel_api_context', ['token' => $contextToken]);

        $this->filesystem->put($feedEntity->getFile(), $content);

        return true;
    }

    private function logError($feed, $exception)
    {
        $this->logger->error("Error exporting Hello Retail $feed feed", [
            [
                'feed' => $feed,
                'errorMsg' => $exception->getMessage(),
                'errorTrace' => $exception->getTraceAsString()
            ]
        ]);
    }

    private function replaceSeoUrlPlaceholder(string $content, SalesChannelContext $salesChannelContext): string
    {
        return $this->seoUrlPlaceholderHandler->replace(
            $content,
            $salesChannelContext->getSalesChannel()->getDomains()->first()->getUrl(),
            $salesChannelContext
        );
    }
}
