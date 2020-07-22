<?php declare(strict_types=1);

namespace Wexo\HelloRetail\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wexo\HelloRetail\Export\Profiles\ProfileExporterInterface;
use Wexo\HelloRetail\WexoHelloRetail;

class GenerateFeed extends Command
{
    protected static $defaultName = 'wexo:hello-retail:generate-feed';

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
            ->addOption('feed', 'f', InputOption::VALUE_REQUIRED, 'Specific feed to generate');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $feed = $input->getOption('feed');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('typeId', WexoHelloRetail::SALES_CHANNEL_TYPE_HELLO_RETAIL));

        $salesChannelId = $this->salesChannelRepository->searchIds(
            $criteria,
            Context::createDefaultContext()
        )->firstId();

        $this->profileExporter->generate($salesChannelId, $feed ? [$feed] : []);

        return 0;
    }
}
