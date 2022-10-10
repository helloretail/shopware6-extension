<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export\Profiles;

use Helret\HelloRetail\Service\ExportService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Exception\SalesChannelNotFoundException;
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
    protected LoggerInterface $logger;
    protected SerializerInterface $serializer;
    protected EntityRepositoryInterface $salesChannelRepository;
    protected HelloRetailService $helloRetailService;
    protected ExportService $exportService;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityRepositoryInterface $salesChannelRepository,
        HelloRetailService $helloRetailService,
        ExportService $exportService
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->helloRetailService = $helloRetailService;
        $this->exportService = $exportService;
    }

    /**
     * @inheritdoc
     */
    public function generate(string $salesChannelId, array $feeds = [], bool $now = false): array
    {
        $salesChannelEntity = $this->salesChannelRepository->search(
            new Criteria([$salesChannelId]),
            Context::createDefaultContext()
        )->first();

        if (!$salesChannelEntity) {
            throw new SalesChannelNotFoundException();
        }

        /** @var ExportEntityInterface $exportEntity */
        $exportEntity = $this->serializer
            ->deserialize(json_encode($salesChannelEntity->getConfiguration()), ExportEntity::class, 'json');

        $notExported = [];
        foreach ($this->exportService->getFeeds() as $key => $feed) {
            if (!empty($feeds) && !in_array($key, $feeds)) {
                $notExported[] = $key;
                continue;
            }

            try {
                $exported = $this->helloRetailService->export($exportEntity, $key);
            } catch (\Exception $e) {
                $exported = false;
            }

            if (!$exported) {
                $notExported[] = $key;
            }
        }

        return $notExported;
    }
}
