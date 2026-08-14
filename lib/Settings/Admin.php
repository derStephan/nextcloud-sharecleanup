<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Settings;

use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Service\CleanupService;
use OCA\ShareCleanup\Service\TagService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;

class Admin implements ISettings {

    public function __construct(
        private IInitialState $initialState,
        private TagService $tagService,
        private CleanupService $cleanupService,
    ) {
    }

    public function getForm(): TemplateResponse {
        $this->initialState->provideInitialState('adminSettings', [
            'maxAgeDays' => $this->tagService->getMaxAgeDays(),
            'notifyDays' => $this->tagService->getNotificationDays(),
            'dryRun' => $this->cleanupService->isDryRun(),
        ]);

        return new TemplateResponse(Application::APP_ID, 'settings/admin', [], 'blank');
    }

    public function getSection(): string {
        return 'sharing';
    }

    public function getPriority(): int {
        return 50;
    }
}
