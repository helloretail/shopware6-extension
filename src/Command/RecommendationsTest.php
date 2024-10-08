<?php declare(strict_types=1);

namespace Helret\HelloRetail\Command;

use Helret\HelloRetail\Service\HelloRetailRecommendationService;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'hello-retail:recommendation:test',
    description: 'Does a test call to the recommendations endpoint'
)]
class RecommendationsTest extends Command
{
    public function __construct(
        private HelloRetailRecommendationService $recommendationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('recommendation', 'r', InputOption::VALUE_REQUIRED, 'Specific recommendation to generate')
            ->addOption('salesChannelId', 's', InputOption::VALUE_OPTIONAL, 'Generate for specific salesChannel');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $recommendationKey = $input->getOption('recommendation');
        $context = Context::createDefaultContext();

        $this->recommendationService->getRecommendations($recommendationKey, $context);

        return self::SUCCESS;
    }
}
