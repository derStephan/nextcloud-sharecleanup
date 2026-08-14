<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Listener;

use OCA\ShareCleanup\Service\TagService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Share\Events\ShareCreatedEvent;
use Psr\Log\LoggerInterface;

/**
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

        // Never tag shares that carry their own expiration date.
        if ($share->getExpirationDate() !== null) {
            return;
        }

        try {
            $this->tagService->tagFileForShare($share);
        } catch (\Throwable $e) {
            $this->logger->error(
                'ShareCleanup: failed to tag share {id}: {msg}',
                ['id' => $share->getId(), 'msg' => $e->getMessage(), 'app' => 'sharecleanup']
            );
        }
    }
}
