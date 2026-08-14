<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\BackgroundJob;

use OCA\ShareCleanup\Service\CleanupService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class CleanupJob extends TimedJob {

    public function __construct(
        ITimeFactory $time,
        private CleanupService $cleanupService,
    ) {
        parent::__construct($time);
        $this->setInterval(3600); // hourly
    }

    protected function run($argument): void {
        $this->cleanupService->run();
    }
}
