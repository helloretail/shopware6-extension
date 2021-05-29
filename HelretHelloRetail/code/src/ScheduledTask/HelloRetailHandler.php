<?php declare(strict_types=1);

namespace Helret\HelloRetail\ScheduledTask;

use Helret\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Helret\HelloRetail\HelretHelloRetail;
use Helret\HelloRetail\Service\HelloRetailService;
use mysql_xdevapi\Exception;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;

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
    private SystemConfigService $configService;

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
        HelloRetailService $helloRetailService,
        SystemConfigService $configService
    ) {
        $this->profileExporter = $profileExporter;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->helloRetailService = $helloRetailService;
        $this->configService = $configService;

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

        $salesChannelIds = $this->salesChannelRepository->searchIds(
            $criteria,
            Context::createDefaultContext()
        );

        try {
            foreach ($salesChannelIds->getIds() as $salesChannelId) {
                /* check settings for each */
                $feeds = $this->getFeedsThatShouldRunNow($salesChannelId);
                if (count($feeds) > 0) {
                    $this->profileExporter->generate($salesChannelId, $feeds);
                }
            }
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

    /**
     * @param string|null $type
     * @return array
     */
    private function getIntervalsInSeconds(?string $type, string $salesChannelId): array
    {
        /* returns intervals in seconds for order and product */
        $intervals = [];

        if ($type == null || !in_array($type, array_keys(HelretHelloRetail::CONFIG_FIELDS))) {
            return $intervals;
        }

        try {
            $fields = $this->getSettingsFromSystemConfig($type, HelretHelloRetail::CONFIG_FIELDS, $salesChannelId);
            /* get the fields from systemConfig now */
            foreach ($fields as $feedIntervalSettings) {
                $format = (string)$feedIntervalSettings['format'];
                $amount = (int)$feedIntervalSettings['amount'];

                if ($format == "hours") {
                    array_push($intervals, $this->getTimeTilNextRun($amount * 60 * 60));
                } elseif ($format == "minutes") {
                    array_push($intervals, $this->getTimeTilNextRun($amount * 60));
                }
            }
        } catch (\Exception $e) {
            $this->helloRetailService->exportLogger(
                HelretHelloRetail::EXPORT_ERROR,
                [
                    'error' => $e->getMessage(),
                    'errorTrace' => $e->getTraceAsString(),
                    'errorType' => get_class($e)
                ]
            );
        }
        return $intervals;
    }

    /**
     * @param string|null $type
     * @return array|string[][][]
     */
    private function getSettingsFromSystemConfig(string $type, ?array $fields, $salesChannelId): array
    {
        $valueFields = [];
        if ($fields == null) {
            /* return default */
            return $valueFields;
        }
        /* get configFields from systemConfigService */
        $configFields = $this->configService->get("HelretHelloRetail.config", $salesChannelId);
        /* shake out the required fields */
        foreach ($fields[$type] as $settingsFields) {
            $formatKey = $settingsFields['format'];
            $amountKey = $settingsFields['amount'];

            if (isset($configFields[$formatKey]) && isset($configFields[$amountKey])) {
                $valueFields[] = [
                    "format" => $configFields[$formatKey],
                    "amount" => $configFields[$amountKey]
                ];
            }
        }
        return $valueFields;
    }

    /**
     * @param string $salesChannelId
     * @return array
     */
    private function getFeedsThatShouldRunNow(string $salesChannelId): array
    {
        $feeds = [];
        foreach (array_keys(HelretHelloRetail::CONFIG_FIELDS) as $feedName) {
            $intervals = $this->getIntervalsInSeconds($feedName, $salesChannelId);

            /* if intervals not an empty array, then get closest run */
            if (!empty($intervals)) {
                $nextRun = min($intervals);
            } else {
                /* else make sure it wont run, by setting a value above the run buffer */
                $nextRun = HelloRetailTask::getDefaultInterval() +1;
            }

            /* if within the interval of this task, its time to run */
            if ($nextRun <= HelloRetailTask::getDefaultInterval()) {
                array_push($feeds, $feedName);
            }
        }
        return $feeds;
    }

    /**
     * @param int $interval
     * @return int
     */
    private function getTimeTilNextRun(int $interval): int
    {
        /* return seconds until next run */
        if ($interval != 0) {
            return abs((time() % $interval) - $interval);
        }
        /* if interval is 0, then never run it! */
        return HelloRetailTask::getDefaultInterval() + 1;
    }
}
