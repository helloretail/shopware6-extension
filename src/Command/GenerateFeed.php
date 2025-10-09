<?php declare(strict_types=1);

namespace Helret\HelloRetail\Command;

use Helret\HelloRetail\Service\ExportService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Exception\SalesChannelNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Helret\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Helret\HelloRetail\HelretHelloRetail;

#[AsCommand(
    name: 'hello-retail:generate-feed',
    description: 'Generates all configured Hello Retail feeds'
)]
class GenerateFeed extends Command
{
    public function __construct(
        protected ProfileExporterInterface $profileExporter,
        protected EntityRepository $salesChannelRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('feed', 'f', InputOption::VALUE_REQUIRED, 'Specific feed to generate')
            ->addOption("salesChannelId", "s", InputOption::VALUE_REQUIRED, "Generate for specific salesChannel");
    }

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
