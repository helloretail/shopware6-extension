<?php declare(strict_types=1);

namespace Helret\HelloRetail\Export\Profiles;

use League\Flysystem\FilesystemException;
use Helret\HelloRetail\Service\ExportService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Exception\SalesChannelNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;
use Helret\HelloRetail\Export\ExportEntity;
use Helret\HelloRetail\Export\ExportEntityInterface;
use Helret\HelloRetail\Service\HelloRetailService;

class ProfileExporter implements ProfileExporterInterface
{
    public function __construct(
        protected LoggerInterface $logger,
        protected SerializerInterface $serializer,
        protected EntityRepository $salesChannelRepository,
        protected HelloRetailService $helloRetailService,
        protected ExportService $exportService
    ) {
    }

    public function generate(string $salesChannelId, array $feeds = [], bool $now = false): array
    {
        $salesChannelEntity = $this->salesChannelRepository->search(
            ExportService::getSalesChannelCriteria([$salesChannelId]),
            Context::createDefaultContext()
        )->first();

        if (!$salesChannelEntity) {
            throw new SalesChannelNotFoundException();
        }

        /**
         * @var ExportEntityInterface $exportEntity
         * @throw FilesystemException
         */
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
            } catch (FilesystemException) {
                $exported = false;
            }

            if (!$exported) {
                $notExported[] = $key;
            }
        }

        return $notExported;
    }
}
