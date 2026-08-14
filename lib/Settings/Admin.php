<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Settings;

use OCA\ShareCleanup\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings {

    public function __construct(
        private IConfig $config,
        private IInitialState $initialState,
    ) {
    }

    public function getForm(): TemplateResponse {
        $this->initialState->provideInitialState(
            Application::APP_ID,
            'max_age_days',
            (int)$this->config->getAppValue(Application::APP_ID, 'max_age_days', '365')
        );
        $this->initialState->provideInitialState(
            Application::APP_ID,
            'dry_run',
            $this->config->getAppValue(Application::APP_ID, 'dry_run', 'yes') === 'yes'
        );

        return new TemplateResponse(Application::APP_ID, 'settings/admin');
    }

    public function getSection(): string {
        return 'sharing';
    }

    public function getPriority(): int {
        return 50;
    }
}
