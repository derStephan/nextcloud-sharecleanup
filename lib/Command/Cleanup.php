<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Command;

use OCA\ShareCleanup\Service\CleanupService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Cleanup extends Command {

    public function __construct(private CleanupService $cleanupService) {
        parent::__construct('sharecleanup:run');
    }

    protected function configure(): void {
        $this
            ->setDescription('Notify about and delete shares older than the configured number of days (default 365)')
            ->addOption(
                'days',
                null,
                InputOption::VALUE_REQUIRED,
                'Override the configured age threshold (days) for this run only'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Only report what would happen, change nothing'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Act for real, ignoring the configured dry-run mode'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $daysOverride = $input->getOption('days') !== null
            ? (int)$input->getOption('days')
            : null;

        $dryRunOverride = null;
        if ($input->getOption('dry-run')) {
            $dryRunOverride = true;
        } elseif ($input->getOption('force')) {
            $dryRunOverride = false;
        }

        $result = $this->cleanupService->run($daysOverride, $dryRunOverride);

        $output->writeln(sprintf(
            '<info>Scanned: %d | Skipped (own expiry): %d | Notified: %d | Shares ended: %d | Failed: %d</info>',
            $result['scanned'],
            $result['skipped_expiry'],
            $result['notified'],
            $result['ended'],
            $result['failed']
        ));

        return $result['failed'] > 0 ? 1 : 0;
    }
}
