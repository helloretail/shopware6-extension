<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export\Profiles;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Serializer\SerializerInterface;
use Helret\HelloRetail\Export\ExportEntity;
use Helret\HelloRetail\Export\ExportEntityInterface;
use Helret\HelloRetail\Service\HelloRetailService;

/**
 * Class ProfileExporter
 * @package Helret\HelloRetail\Export\Profiles
 */
class ProfileExporter implements ProfileExporterInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var EntityRepositoryInterface
     */
    protected $salesChannelRepository;
    /**
     * @var HelloRetailService
     */
    protected $helloRetailService;

    /**
     * ProfileExporter constructor.
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param HelloRetailService $helloRetailService
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityRepositoryInterface $salesChannelRepository,
        HelloRetailService $helloRetailService
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->helloRetailService = $helloRetailService;
    }

    /**
     * @param $salesChannelId
     * @param array $feeds
     * @param bool $now
     * @return array
     */
    public function generate($salesChannelId, array $feeds = [], $now = false): array
    {
        $salesChannelEntity = $this->salesChannelRepository->search(
            new Criteria([$salesChannelId]),
            Context::createDefaultContext()
        )->first();

        /** @var ExportEntityInterface $exportEntity */
        $exportEntity = $this->serializer
            ->deserialize(json_encode($salesChannelEntity->getConfiguration()), ExportEntity::class, 'json');

        $notExported = [];
        foreach ($exportEntity->getFeeds() as $key => $feed) {
            if ((!empty($feeds) && !in_array($key, $feeds))
                || !$feed['file']
                || !$feed['headerTemplate']
                || !$feed['bodyTemplate']
                || !$feed['footerTemplate']
            ) {
                $notExported[] = $key;
                continue;
            }

            $this->helloRetailService->export($exportEntity, $key);
        }

        return $notExported;
    }
}
