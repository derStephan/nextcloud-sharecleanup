<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Listener;

use DateTime;
use OCA\ShareCleanup\Listener\ShareCreatedListener;
use OCA\ShareCleanup\Service\TagService;
use OCP\EventDispatcher\Event;
use OCP\Share\Events\ShareCreatedEvent;
use OCP\Share\IShare;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShareCreatedListenerTest extends TestCase {

    private TagService&MockObject $tagService;
    private LoggerInterface&MockObject $logger;
    private ShareCreatedListener $listener;

    protected function setUp(): void {
        parent::setUp();
        $this->tagService = $this->createMock(TagService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new ShareCreatedListener($this->tagService, $this->logger);
    }

    private function makeEvent(?DateTime $expiration): ShareCreatedEvent {
        $share = $this->createMock(IShare::class);
        $share->method('getExpirationDate')->willReturn($expiration);
        $share->method('getShareTime')->willReturn(new DateTime());
        $share->method('getId')->willReturn('1');
        return new ShareCreatedEvent($share);
    }

    public function testIgnoresNonShareCreatedEvents(): void {
        $this->tagService->expects($this->never())->method('tagFileForShare');
        $this->listener->handle(new Event());
    }

    public function testTagsShareWithoutOwnExpiration(): void {
        $event = $this->makeEvent(null);
        $this->tagService->expects($this->once())->method('tagFileForShare');
        $this->listener->handle($event);
    }

    public function testSkipsShareWithOwnExpiration(): void {
        $event = $this->makeEvent(new DateTime('+10 days'));
        $this->tagService->expects($this->never())->method('tagFileForShare');
        $this->listener->handle($event);
    }

    public function testTaggingFailureDoesNotBubbleUp(): void {
        $event = $this->makeEvent(null);
        $this->tagService->method('tagFileForShare')
            ->willThrowException(new \RuntimeException('tag backend down'));

        $this->logger->expects($this->once())->method('error');

        $this->listener->handle($event);
        $this->addToAssertionCount(1);
    }
}
