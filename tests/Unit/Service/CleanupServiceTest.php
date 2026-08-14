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
        $this->timeFactory = $this->createMock(ITimeFactory::class);
        $this->userManager = $this->createMock(IUserManager::class);
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->tagService = $this->createMock(TagService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new CleanupService(
            $this->shareManager,
            $this->notificationManager,
            $this->config,
            $this->timeFactory,
            $this->userManager,
            $this->rootFolder,
            $this->tagService,
            $this->logger
        );
    }

    public function testIsDryRunDefaultsToYes(): void {
        $this->config->method('getAppValue')
            ->willReturnCallback(fn($app, $key, $default = '') => $default);

        $this->assertTrue($this->service->isDryRun());
    }

    public function testIsDryRunFalseWhenNo(): void {
        $this->config->method('getAppValue')
            ->willReturnCallback(function ($app, $key, $default = '') {
                if ($key === CleanupService::CONFIG_DRY_RUN) return 'no';
                return $default;
            });

        $this->assertFalse($this->service->isDryRun());
    }

    public function testShareWithOwnExpirationIsSkipped(): void {
        $share = $this->createMock(IShare::class);
        $share->method('getId')->willReturn('1');
        $share->method('getShareTime')->willReturn(new DateTime('-100 days'));
        $share->method('getExpirationDate')->willReturn(new DateTime('+10 days'));
        $share->method('getShareType')->willReturn(IShare::TYPE_USER);

        $this->shareManager->method('getAllShares')->willReturn([$share]);
        $this->config->method('getAppValue')
            ->willReturnCallback(fn($app, $key, $default = '') => $default);

        $this->shareManager->expects($this->never())->method('deleteShare');
        $this->notificationManager->expects($this->never())->method('notify');

        $this->service->run();
    }

    public function testExpiredShareIsEndedInLiveMode(): void {
        $share = $this->createMock(IShare::class);
        $share->method('getId')->willReturn('1');
        $share->method('getShareTime')->willReturn(new DateTime('-400 days'));
        $share->method('getExpirationDate')->willReturn(null);
        $share->method('getShareType')->willReturn(IShare::TYPE_USER);
        $share->method('getSharedBy')->willReturn('user1');
        $share->method('getNodeId')->willReturn(123);
        $share->method('getNode')->willReturn(null);

        // Return share only for TYPE_USER, empty for all other types
        $this->shareManager->method('getAllShares')
            ->willReturnCallback(function ($type, $includeExpired, $limit, $offset) use ($share) {
                if ($type === IShare::TYPE_USER && $offset === 0) {
                    return [$share];
                }
                return [];
            });

        $this->config->method('getAppValue')
            ->willReturnCallback(function ($app, $key, $default = '') {
                if ($key === 'max_age_days') return '365';
                if ($key === CleanupService::CONFIG_DRY_RUN) return 'no';
                return $default;
            });

        $this->timeFactory->method('getTime')->willReturn(time());

        // Expect exactly 1 deleteShare call (only for TYPE_USER share)
        $this->shareManager->expects($this->once())->method('deleteShare');

        $this->service->run();
    }

    public function testExpiredShareIsNotEndedInDryRun(): void {
        $share = $this->createMock(IShare::class);
        $share->method('getId')->willReturn('1');
        $share->method('getShareTime')->willReturn(new DateTime('-400 days'));
        $share->method('getExpirationDate')->willReturn(null);
        $share->method('getShareType')->willReturn(IShare::TYPE_USER);
        $share->method('getSharedBy')->willReturn('user1');
        $share->method('getNodeId')->willReturn(123);
        $share->method('getNode')->willReturn(null);

        $this->shareManager->method('getAllShares')
            ->willReturnCallback(function ($type, $includeExpired, $limit, $offset) use ($share) {
                if ($type === IShare::TYPE_USER && $offset === 0) {
                    return [$share];
                }
                return [];
            });

        $this->config->method('getAppValue')
            ->willReturnCallback(function ($app, $key, $default = '') {
                if ($key === 'max_age_days') return '365';
                if ($key === CleanupService::CONFIG_DRY_RUN) return 'yes';
                return $default;
            });

        $this->timeFactory->method('getTime')->willReturn(time());

        $this->shareManager->expects($this->never())->method('deleteShare');

        $this->service->run();
    }

    public function testYoungShareIsUntouched(): void {
        $share = $this->createMock(IShare::class);
        $share->method('getId')->willReturn('1');
        $share->method('getShareTime')->willReturn(new DateTime('-10 days'));
        $share->method('getExpirationDate')->willReturn(null);
        $share->method('getShareType')->willReturn(IShare::TYPE_USER);

        $this->shareManager->method('getAllShares')
            ->willReturnCallback(function ($type, $includeExpired, $limit, $offset) use ($share) {
                if ($type === IShare::TYPE_USER && $offset === 0) {
                    return [$share];
                }
                return [];
            });

        $this->config->method('getAppValue')
            ->willReturnCallback(fn($app, $key, $default = '') => $default);

        $this->shareManager->expects($this->never())->method('deleteShare');
        $this->notificationManager->expects($this->never())->method('notify');

        $this->service->run();
    }

    public function testShareInNotificationWindowIsNotified(): void {
        $share = $this->createMock(IShare::class);
        $share->method('getId')->willReturn('1');
        $share->method('getShareTime')->willReturn(new DateTime('-330 days'));
        $share->method('getExpirationDate')->willReturn(null);
        $share->method('getShareType')->willReturn(IShare::TYPE_USER);
        $share->method('getSharedBy')->willReturn('user1');
        $share->method('getNodeId')->willReturn(123);
        $share->method('getNode')->willReturn(null);

        $this->shareManager->method('getAllShares')
            ->willReturnCallback(function ($type, $includeExpired, $limit, $offset) use ($share) {
                if ($type === IShare::TYPE_USER && $offset === 0) {
                    return [$share];
                }
                return [];
            });

        $this->config->method('getAppValue')
            ->willReturnCallback(function ($app, $key, $default = '') {
                if ($key === 'max_age_days') return '365';
                if ($key === CleanupService::CONFIG_DRY_RUN) return 'no';
                return $default;
            });

        $this->timeFactory->method('getTime')->willReturn(time());

        $this->notificationManager->expects($this->once())->method('notify');
        $this->shareManager->expects($this->never())->method('deleteShare');

        $this->service->run();
    }
}
