<?php

namespace Wexo\HelloRetail\Export\Profiles;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Wexo\HelloRetail\Component\MessageQueue\HelloRetailExport;
use Wexo\HelloRetail\Component\MessageQueue\HelloRetailExportHandler;
use Wexo\HelloRetail\Export\ExportEntity;
use Wexo\HelloRetail\Export\ExportEntityInterface;

class ProfileExporter implements ProfileExporterInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var HelloRetailExportHandler
     */
    protected $exportHandler;

    /**
     * @var EntityRepositoryInterface
     */
    protected $salesChannelRepository;

    /**
     * ProfileExporter constructor.
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param MessageBusInterface $messageBus
     * @param HelloRetailExportHandler $exportHandler
     * @param EntityRepositoryInterface $salesChannelRepository
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        MessageBusInterface $messageBus,
        HelloRetailExportHandler $exportHandler,
        EntityRepositoryInterface $salesChannelRepository
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->messageBus = $messageBus;
        $this->exportHandler = $exportHandler;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * @param $salesChannelId
     * @param array $feeds
     * @param bool $now
     * @return array
     */
    public function generate($salesChannelId, array $feeds = [], $now = false): array
    {
        $salesChannelEntity = $this->salesChannelRepository->search(new Criteria([$salesChannelId]), Context::createDefaultContext())->first();

        /** @var ExportEntityInterface $exportEntity */
        $exportEntity = $this->serializer
            ->deserialize(json_encode($salesChannelEntity->getConfiguration()), ExportEntity::class, 'json');

        $notExported = [];

        foreach ($exportEntity->getFeeds() as $key => $feed) {
            if ((!empty($feeds) && !in_array($key, $feeds)) || !$feed['file'] || !$feed['template']) {
                $notExported[] = $key;
                continue;
            }

            $message = new HelloRetailExport($exportEntity, $key);

            if (!$now) {
                $this->messageBus->dispatch($message);
            } else {
                $this->exportHandler->handle($message);
            }
        }

        return $notExported;
    }
}
