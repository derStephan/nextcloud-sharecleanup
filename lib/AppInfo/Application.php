<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\AppInfo;

use OCA\ShareCleanup\Listener\ShareCreatedListener;
use OCA\ShareCleanup\Notification\Notifier;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Share\Events\ShareCreatedEvent;

class Application extends App implements IBootstrap {

    public const APP_ID = 'sharecleanup';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Tag newly created shares with the end date of the share.
        $context->registerEventListener(ShareCreatedEvent::class, ShareCreatedListener::class);

        // Register the notification provider for advance end-of-share warnings.
        $context->registerNotifierService(Notifier::class);
    }

    public function boot(IBootContext $context): void {
    }
}
