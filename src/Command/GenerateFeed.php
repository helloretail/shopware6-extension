<?php declare(strict_types=1);

namespace Helret\HelloRetail\Command;

use Helret\HelloRetail\Service\ExportService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Exception\SalesChannelNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Helret\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Helret\HelloRetail\HelretHelloRetail;

/**
 * Class GenerateFeed
 * @package Helret\HelloRetail\Command
 */
class GenerateFeed extends Command
{
    protected static $defaultName = 'hello-retail:generate-feed';
    protected ProfileExporterInterface $profileExporter;
    protected EntityRepositoryInterface $salesChannelRepository;

    /**
     * GenerateFeed constructor.
     * @param ProfileExporterInterface $profileExporter
     * @param EntityRepositoryInterface $salesChannelRepository
     */
    public function __construct(
        ProfileExporterInterface $profileExporter,
        EntityRepositoryInterface $salesChannelRepository
    ) {
        $this->profileExporter = $profileExporter;
        $this->salesChannelRepository = $salesChannelRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates all configured Hello Retail feeds')
            ->addOption('feed', 'f', InputOption::VALUE_REQUIRED, 'Specific feed to generate')
            ->addOption("salesChannelId", "s", InputOption::VALUE_REQUIRED, "Generate for specific salesChannel");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $feed = $input->getOption('feed');

        $salesChannelId = $input->getOption('salesChannelId') ? [$input->getOption('salesChannelId')] : null;
        $salesChannelIds = $this->salesChannelRepository->searchIds(
            ExportService::getSalesChannelCriteria($salesChannelId),
            Context::createDefaultContext()
        )->getIds();

        foreach ($salesChannelIds as $salesChannelId) {
            try {
                $this->profileExporter->generate($salesChannelId, $feed ? [$feed] : []);
                $output->writeln("Feed(s) for sales channel: $salesChannelId were queued for generation");
            } catch (\Error|\TypeError|\Exception|SalesChannelNotFoundException $exception) {
                $msg = "Msg: {$exception->getMessage()}, ln: {$exception->getLine()}, File: {$exception->getFile()}";
                $output->writeln($msg);
            }
        }

        if (!$salesChannelIds) {
            $output->writeln([
                "No active sales channel(s) with type id: were found",
                HelretHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL . PHP_EOL,
                "Therefore skipping feed generation" . PHP_EOL
            ]);
        }

        return 0;
    }
}
