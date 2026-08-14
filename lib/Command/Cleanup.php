<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Command;

use OCA\ShareCleanup\Service\CleanupService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Cleanup extends Command {

    public function __construct(
        private CleanupService $cleanupService,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setName('sharecleanup:run')
            ->setDescription('Run the share cleanup manually')
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Override max age in days')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only log, do not change anything')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Run even if dry-run is enabled in settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $days = $input->getOption('days');
        $dryRun = $input->getOption('dry-run') || !$input->getOption('force');

        $daysOverride = $days !== null ? (int)$days : null;
        $dryRunOverride = $input->getOption('force') ? false : ($input->getOption('dry-run') ? true : null);

        $result = $this->cleanupService->run($daysOverride, $dryRunOverride);

        $output->writeln(
            sprintf(
                '<info>Scanned: %d | Skipped (own expiry): %d | Notified: %d | Shares ended: %d | Failed: %d</info>',
                $result['scanned'],
                $result['skipped_expiry'],
                $result['notified'],
                $result['ended'],
                $result['failed']
            )
        );

        return 0;
    }
}
