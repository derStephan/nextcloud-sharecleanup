<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Service;

use DateTime;
use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Service\TagService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\Node;
use OCP\IConfig;
use OCP\Share\IShare;
use OCP\SystemTag\ISystemTag;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TagServiceExtendedTest extends TestCase {

    private IConfig&MockObject $config;
    private ISystemTagManager&MockObject $tagManager;
    private ISystemTagObjectMapper&MockObject $tagMapper;
    private ITimeFactory&MockObject $timeFactory;
    private LoggerInterface&MockObject $logger;
    private TagService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->config = $this->createMock(IConfig::class);
        $this->tagManager = $this->createMock(ISystemTagManager::class);
        $this->tagMapper = $this->createMock(ISystemTagObjectMapper::class);
        $this->timeFactory = $this->createMock(ITimeFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new TagService(
            $this->config,
            $this->tagManager,
            $this->tagMapper,
            $this->timeFactory,
            $this->logger
        );
    }

    // --- tagNameForDate ---

    public function testTagNameForDateFormat(): void {
        $date = new DateTime('2026-09-15');
        $this->assertSame('Share ends on 2026-09-15', $this->service->tagNameForDate($date));
    }

    // --- tagFileForShare ---

    public function testTagFileForShareCreatesAndAssignsTag(): void {
        $this->config->method('getAppValue')->willReturn('365');

        $node = $this->createMock(Node::class);
        $node->method('getId')->willReturn(42);
        $node->method('getName')->willReturn('document.pdf');

        $share = $this->createMock(IShare::class);
        $share->method('getNode')->willReturn($node);
        $share->method('getShareTime')->willReturn(new DateTime('2026-01-01'));

        $tag = $this->createMock(ISystemTag::class);
        $tag->method('getId')->willReturn('tag-123');
        $tag->method('getName')->willReturn('Share ends on 2027-01-01');

        $this->tagManager->method('matchingTagsByName')
            ->with('Share ends on 2027-01-01')
            ->willReturn([$tag]);

        $this->tagMapper->expects($this->once())
            ->method('assignTags')
            ->with('42', 'files', ['tag-123']);

        $this->service->tagFileForShare($share);
    }

    public function testTagFileForShareSkipsWhenNoShareTime(): void {
        $share = $this->createMock(IShare::class);
        $share->method('getNode')->willReturn($this->createMock(Node::class));
        $share->method('getShareTime')->willReturn(null);

        $this->tagManager->expects($this->never())->method('matchingTagsByName');
        $this->tagMapper->expects($this->never())->method('assignTags');

        $this->service->tagFileForShare($share);
    }

    public function testTagFileForShareCreatesNewTagWhenNotFound(): void {
        $this->config->method('getAppValue')->willReturn('30');

        $node = $this->createMock(Node::class);
        $node->method('getId')->willReturn(99);
        $node->method('getName')->willReturn('photo.jpg');

        $share = $this->createMock(IShare::class);
        $share->method('getNode')->willReturn($node);
        $share->method('getShareTime')->willReturn(new DateTime('2026-06-01'));

        $newTag = $this->createMock(ISystemTag::class);
        $newTag->method('getId')->willReturn('new-tag-456');

        // No existing tag found
        $this->tagManager->method('matchingTagsByName')
            ->willReturn([]);

        $this->tagManager->expects($this->once())
            ->method('createTag')
            ->with('Share ends on 2026-07-01', false, false)
            ->willReturn($newTag);

        $this->tagMapper->expects($this->once())
            ->method('assignTags')
            ->with('99', 'files', ['new-tag-456']);

        $this->service->tagFileForShare($share);
    }

    // --- cleanupPastTags ---

    public function testCleanupPastTagsRemovesExpiredTags(): void {
        $now = new DateTime('2026-08-15');
        $this->timeFactory->method('getTime')->willReturn($now->getTimestamp());

        $pastTag = $this->createMock(ISystemTag::class);
        $pastTag->method('getId')->willReturn('tag-old');
        $pastTag->method('getName')->willReturn('Share ends on 2026-08-01');

        $futureTag = $this->createMock(ISystemTag::class);
        $futureTag->method('getId')->willReturn('tag-future');
        $futureTag->method('getName')->willReturn('Share ends on 2026-09-01');

        $this->tagManager->method('matchingTagsByName')
            ->with('Share ends on %')
            ->willReturn([$pastTag, $futureTag]);

        // Past tag has no objects assigned
        $this->tagMapper->method('getObjectIdsForTags')
            ->with(['tag-old'], 'files')
            ->willReturn([]);

        $this->tagManager->expects($this->once())
            ->method('deleteTags')
            ->with(['tag-old']);

        $removed = $this->service->cleanupPastTags();

        $this->assertSame(1, $removed);
    }

    public function testCleanupPastTagsKeepsTagsWithObjects(): void {
        $now = new DateTime('2026-08-15');
        $this->timeFactory->method('getTime')->willReturn($now->getTimestamp());

        $pastTag = $this->createMock(ISystemTag::class);
        $pastTag->method('getId')->willReturn('tag-old');
        $pastTag->method('getName')->willReturn('Share ends on 2026-08-01');

        $this->tagManager->method('matchingTagsByName')
            ->willReturn([$pastTag]);

        // Past tag still has objects assigned
        $this->tagMapper->method('getObjectIdsForTags')
            ->with(['tag-old'], 'files')
            ->willReturn(['file-1', 'file-2']);

        $this->tagManager->expects($this->never())->method('deleteTags');

        $removed = $this->service->cleanupPastTags();

        $this->assertSame(0, $removed);
    }

    public function testCleanupPastTagsIgnoresInvalidDateFormats(): void {
        $now = new DateTime('2026-08-15');
        $this->timeFactory->method('getTime')->willReturn($now->getTimestamp());

        $invalidTag = $this->createMock(ISystemTag::class);
        $invalidTag->method('getId')->willReturn('tag-invalid');
        $invalidTag->method('getName')->willReturn('Share ends on not-a-date');

        $this->tagManager->method('matchingTagsByName')
            ->willReturn([$invalidTag]);

        $this->tagManager->expects($this->never())->method('deleteTags');

        $removed = $this->service->cleanupPastTags();

        $this->assertSame(0, $removed);
    }

    public function testCleanupPastTagsIgnoresNonMatchingTags(): void {
        $now = new DateTime('2026-08-15');
        $this->timeFactory->method('getTime')->willReturn($now->getTimestamp());

        $otherTag = $this->createMock(ISystemTag::class);
        $otherTag->method('getId')->willReturn('tag-other');
        $otherTag->method('getName')->willReturn('Some other tag');

        $this->tagManager->method('matchingTagsByName')
            ->willReturn([$otherTag]);

        $this->tagManager->expects($this->never())->method('deleteTags');

        $removed = $this->service->cleanupPastTags();

        $this->assertSame(0, $removed);
    }
}
