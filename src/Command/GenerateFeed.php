<?php

namespace Wexo\HelloRetail\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wexo\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Wexo\HelloRetail\WexoHelloRetail;

class GenerateFeed extends Command
{
    protected static $defaultName = 'wexo:generate-feed';

    /**
     * @var ProfileExporterInterface
     */
    protected $profileExporter;

    /**
     * @var EntityRepositoryInterface
     */
    protected $salesChannelRepository;

    /**
     * GenerateFeed constructor.
     * @param ProfileExporterInterface $profileExporter
     * @param EntityRepositoryInterface $salesChannelRepository
     */
    public function __construct(ProfileExporterInterface $profileExporter, EntityRepositoryInterface $salesChannelRepository)
    {
        $this->profileExporter = $profileExporter;
        $this->salesChannelRepository = $salesChannelRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('name', InputArgument::REQUIRED, 'Sales Channel Name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        if (!$name) {
            $output->writeln('Argument name is missing');
            return 0;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('typeId', WexoHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL));
        $criteria->addFilter(new EqualsFilter('name', $name));

        $salesChannelId = $this->salesChannelRepository->searchIds($criteria, Context::createDefaultContext())->firstId();

        $output->writeln('Starting generation');
        $this->profileExporter->generate($salesChannelId);

        return 0;
    }
}
