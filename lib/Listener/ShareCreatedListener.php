<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Listener;

use OCA\ShareCleanup\Service\TagService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Share\Events\ShareCreatedEvent;
use Psr\Log\LoggerInterface;

/**
 * Tags the shared file/folder at share creation time with the date on which
 * this app will end the share. One tag per share, so multiple shares of the
 * same file each carry their own end date.
 *
 * Shares that carry their own expiration date are excluded — they are never
 * ended by the cleanup and therefore never tagged.
 *
 * @template-implements IEventListener<ShareCreatedEvent>
 */
class ShareCreatedListener implements IEventListener {

    public function __construct(
        private TagService $tagService,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(Event $event): void {
        if (!($event instanceof ShareCreatedEvent)) {
            return;
        }

        $share = $event->getShare();

        // Shares with their own expiration date are managed by Nextcloud, not by us.
        if ($share->getExpirationDate() !== null) {
            return;
        }

        try {
            $this->tagService->tagFileForShare($share);
        } catch (\Throwable $e) {
            // Tagging must never break share creation.
            $this->logger->error(
                'ShareCleanup: failed to tag file for share {id}: {msg}',
                ['id' => $share->getId(), 'msg' => $e->getMessage(), 'app' => 'sharecleanup']
            );
        }
    }
}
