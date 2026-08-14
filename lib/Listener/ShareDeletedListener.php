<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Listener;

use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Service\CleanupService;
use OCA\ShareCleanup\Service\TagService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\Share\Events\ShareDeletedEvent;
use OCP\Share\IShare;
use Psr\Log\LoggerInterface;

/**
 * Removes the "Freigabe endet am …" tag when a share is deleted manually
 * (not via the cleanup job). Keeps file tags in sync with actual shares.
 *
 * @template-implements IEventListener<ShareDeletedEvent>
 */
class ShareDeletedListener implements IEventListener {

    public function __construct(
        private TagService $tagService,
        private IConfig $config,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(Event $event): void {
        if (!($event instanceof ShareDeletedEvent)) {
            return;
        }

        $share = $event->getShare();

        // Shares with their own expiration date were never tagged.
        if ($share->getExpirationDate() !== null) {
            return;
        }

        try {
            $this->tagService->removeTagForShare($share);

            // Also drop any stored notification marker for this share.
            $this->config->deleteAppValue(
                Application::APP_ID,
                CleanupService::PREFIX_NOTIFIED . $share->getId()
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                'ShareCleanup: failed to remove tag for deleted share {id}: {msg}',
                ['id' => $share->getId(), 'msg' => $e->getMessage(), 'app' => 'sharecleanup']
            );
        }
    }
}
