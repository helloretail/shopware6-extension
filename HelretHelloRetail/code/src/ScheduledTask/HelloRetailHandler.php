<?php declare(strict_types=1);

namespace Helret\HelloRetail\ScheduledTask;

use Helret\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Helret\HelloRetail\HelretHelloRetail;
use Helret\HelloRetail\Service\HelloRetailService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

/**
 * Class HelloRetailHandler
 * @package Helret\HelloRetail\ScheduledTask
 */
class HelloRetailHandler extends ScheduledTaskHandler
{
    /**
     * @var ProfileExporterInterface
     */
    protected $profileExporter;

    /**
     * @var EntityRepositoryInterface
     */
    protected $salesChannelRepository;

    /**
     * @var HelloRetailService
     */
    protected $helloRetailService;

    /**
     * HelloRetailHandler constructor.
     * @param EntityRepositoryInterface $scheduledTaskRepository
     * @param ProfileExporterInterface $profileExporter
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param HelloRetailService $helloRetailService
     */
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ProfileExporterInterface $profileExporter,
        EntityRepositoryInterface $salesChannelRepository,
        HelloRetailService $helloRetailService
    ) {
        $this->profileExporter = $profileExporter;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->helloRetailService = $helloRetailService;

        parent::__construct($scheduledTaskRepository);
    }

    /**
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [HelloRetailTask::class];
    }

    public function run(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('typeId', HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL));

        $salesChannelId = $this->salesChannelRepository->searchIds(
            $criteria,
            Context::createDefaultContext()
        )->firstId();

        try {
            $this->profileExporter->generate($salesChannelId, []);
        } catch (\Error | \TypeError | \Exception $e) {
            $this->helloRetailService->exportLogger(
                HelretHelloRetail::EXPORT_ERROR,
                [
                    'error' => $e->getMessage(),
                    'errorTrace' => $e->getTraceAsString(),
                    'errorType' => get_class($e)
                ]
            );
        }
    }
}
