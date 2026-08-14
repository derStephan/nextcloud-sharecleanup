<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Controller;

use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Service\CleanupService;
use OCA\ShareCleanup\Service\TagService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private IConfig $config,
        private LoggerInterface $logger,
    ) {
        parent::__construct($appName, $request);
    }

    #[AuthorizedAdminSetting(settings: \OCA\ShareCleanup\Settings\Admin::class)]
    public function save(int $maxAgeDays, bool $dryRun): JSONResponse {
        $maxAgeDays = max(1, min(3650, $maxAgeDays));

        $this->config->setAppValue(Application::APP_ID, TagService::CONFIG_DAYS, (string)$maxAgeDays);
        $this->config->setAppValue(Application::APP_ID, CleanupService::CONFIG_DRY_RUN, $dryRun ? 'yes' : 'no');

        $this->logger->info(
            'ShareCleanup: settings updated (max age: {days} days, dry-run: {dry})',
            ['days' => $maxAgeDays, 'dry' => $dryRun ? 'yes' : 'no', 'app' => 'sharecleanup']
        );

        return new JSONResponse(['status' => 'ok']);
    }
}
