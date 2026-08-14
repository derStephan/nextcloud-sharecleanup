<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Service;

use DateTime;
use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Service\CleanupService;
use OCA\ShareCleanup\Service\TagService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IUserManager;
use OCP\Notification\IManager as INotificationManager;
use OCP\Share\IManager as IShareManager;
use OCP\Share\IShare;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CleanupServiceTest extends TestCase {

    private IShareManager&MockObject $shareManager;
    private INotificationManager&MockObject $notificationManager;
    private IConfig&MockObject $config;
    private IDBConnection&MockObject $db;
    private ITimeFactory&MockObject $timeFactory;
    private IUserManager&MockObject $userManager;
    private IRootFolder&MockObject $rootFolder;
    private TagService&MockObject $tagService;
    private LoggerInterface&MockObject $logger;
    private CleanupService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->shareManager = $this->createMock(IShareManager::class);
        $this->notificationManager = $this->createMock(INotificationManager::class);
        $this->config = $this->createMock(IConfig::class);
        $this->db = $this->createMock(IDBConnection::class);
        $this->timeFactory = $this->createMock(ITimeFactory::class);
        $this->userManager = $this->createMock(IUserManager::class);
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->tagService = $this->createMock(TagService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new CleanupService(
            $this->shareManager,
            $this->notificationManager,
            $this->config,
            $this->db,
            $this->timeFactory,
            $this->userManager,
            $this->rootFolder,
            $this->tagService,
            $this->logger
        );
    }

    private function makeShare(?DateTime $shareTime, ?DateTime $expiration, string $id = '1'): IShare&MockObject {
        $share = $this->createMock(IShare::class);
        $share->method('getId')->willReturn($id);
        $share->method('getShareTime')->willReturn($shareTime);
        $share->method('getExpirationDate')->willReturn($expiration);
        $share->method('getShareType')->willReturn(IShare::TYPE_USER);
        $share->method('getNodeId')->willReturn(123);
        $share->method('getSharedBy')->willReturn('alice');
        return $share;
    }

    public function testIsDryRunDefaultsToYes(): void {
        $this->config->method('getAppValue')->willReturn('yes');
        $this->assertTrue($this->service->isDryRun());
    }

    public function testIsDryRunFalseWhenNo(): void {
        $this->config->method('getAppValue')->willReturn('no');
        $this->assertFalse($this->service->isDryRun());
    }

    public function testShareWithOwnExpirationIsSkipped(): void {
        // A share that HAS its own expiration date must never be ended or notified.
        $oldTime = (new DateTime())->modify('-400 days');
        $ownExpiry = (new DateTime())->modify('+10 days');
        $share = $this->makeShare($oldTime, $ownExpiry);

        $this->tagService->method('getMaxAgeDays')->willReturn(365);
        $this->timeFactory->method('getTime')->willReturn(time());

        // discoverShareTypes: make DB return one type so the loop runs.
        $this->mockShareTypeDiscovery([IShare::TYPE_USER]);
        $this->shareManager->method('getAllShares')->willReturn([$share]);

        // Never delete, never notify.
        $this->shareManager->expects($this->never())->method('deleteShare');
        $this->notificationManager->expects($this->never())->method('notify');

        $result = $this->service->run(null, false);

        $this->assertSame(1, $result['skipped_expiry']);
        $this->assertSame(0, $result['ended']);
        $this->assertSame(0, $result['notified']);
    }

    public function testExpiredShareIsEndedInLiveMode(): void {
        $oldTime = (new DateTime())->modify('-400 days');
        $share = $this->makeShare($oldTime, null, '42');

        $this->tagService->method('getMaxAgeDays')->willReturn(365);
        $this->timeFactory->method('getTime')->willReturn(time());
        $this->mockShareTypeDiscovery([IShare::TYPE_USER]);
        $this->shareManager->method('getAllShares')->willReturn([$share]);

        $this->shareManager->expects($this->once())->method('deleteShare')->with($share);

        $result = $this->service->run(null, false);

        $this->assertSame(1, $result['ended']);
    }

    public function testExpiredShareIsNotEndedInDryRun(): void {
        $oldTime = (new DateTime())->modify('-400 days');
        $share = $this->makeShare($oldTime, null, '42');

        $this->tagService->method('getMaxAgeDays')->willReturn(365);
        $this->timeFactory->method('getTime')->willReturn(time());
        $this->mockShareTypeDiscovery([IShare::TYPE_USER]);
        $this->shareManager->method('getAllShares')->willReturn([$share]);

        // Dry-run: count as "would end" but never call deleteShare.
        $this->shareManager->expects($this->never())->method('deleteShare');

        $result = $this->service->run(null, true);

        $this->assertSame(1, $result['ended']); // counted, but not actually deleted
    }

    public function testYoungShareIsUntouched(): void {
        $recentTime = (new DateTime())->modify('-10 days');
        $share = $this->makeShare($recentTime, null);

        $this->tagService->method('getMaxAgeDays')->willReturn(365);
        $this->timeFactory->method('getTime')->willReturn(time());
        $this->mockShareTypeDiscovery([IShare::TYPE_USER]);
        $this->shareManager->method('getAllShares')->willReturn([$share]);

        $this->shareManager->expects($this->never())->method('deleteShare');
        $this->notificationManager->expects($this->never())->method('notify');

        $result = $this->service->run(null, false);

        $this->assertSame(0, $result['ended']);
        $this->assertSame(0, $result['notified']);
        $this->assertSame(1, $result['scanned']);
    }

    public function testShareInNotificationWindowIsNotified(): void {
        // 350 days old: past 90% (328) but not yet past 365 → notify, don't end.
        $notifyTime = (new DateTime())->modify('-350 days');
        $share = $this->makeShare($notifyTime, null, '77');

        $this->tagService->method('getMaxAgeDays')->willReturn(365);
        $this->timeFactory->method('getTime')->willReturn(time());
        $this->mockShareTypeDiscovery([IShare::TYPE_USER]);
        $this->shareManager->method('getAllShares')->willReturn([$share]);
        $this->userManager->method('userExists')->willReturn(true);
        // Not yet notified (marker absent).
        $this->config->method('getAppValue')->willReturn('');

        $notification = $this->createMock(\OCP\Notification\INotification::class);
        $notification->method('setApp')->willReturnSelf();
        $notification->method('setUser')->willReturnSelf();
        $notification->method('setDateTime')->willReturnSelf();
        $notification->method('setObject')->willReturnSelf();
        $notification->method('setSubject')->willReturnSelf();
        $this->notificationManager->method('createNotification')->willReturn($notification);

        $this->notificationManager->expects($this->once())->method('notify');
        $this->shareManager->expects($this->never())->method('deleteShare');

        $result = $this->service->run(null, false);

        $this->assertSame(1, $result['notified']);
        $this->assertSame(0, $result['ended']);
    }

    /**
     * Make the DB query builder return the given share types for discovery.
     */
    private function mockShareTypeDiscovery(array $types): void {
        $result = $this->createMock(\OCP\DB\IResult::class);
        $rows = array_map(fn($t) => ['share_type' => $t], $types);
        $result->method('fetch')->willReturnOnConsecutiveCalls(...array_merge($rows, [false]));

        $qb = $this->createMock(\OCP\DB\QueryBuilder\IQueryBuilder::class);
        $qb->method('selectDistinct')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('executeQuery')->willReturn($result);

        $this->db->method('getQueryBuilder')->willReturn($qb);
    }
}
