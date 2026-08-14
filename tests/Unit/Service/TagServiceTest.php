<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Service;

use DateTime;
use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Service\TagService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TagServiceTest extends TestCase {

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

    public function testDefaultMaxAgeIs365(): void {
        $this->config->method('getAppValue')
            ->willReturnCallback(fn($app, $key, $default = '') => $default);

        $this->assertSame(365, $this->service->getMaxAgeDays());
    }

    public function testMaxAgeFallsBackToDefaultWhenUnset(): void {
        // When the config value is not set, getAppValue returns the default ('365')
        $this->config->method('getAppValue')
            ->willReturn('365');

        $this->assertSame(365, $this->service->getMaxAgeDays());
    }

    public function testMaxAgeIsAtLeastOne(): void {
        $this->config->method('getAppValue')
            ->willReturn('0');

        $this->assertSame(1, $this->service->getMaxAgeDays());
    }

    public function testCustomMaxAge(): void {
        $this->config->method('getAppValue')
            ->willReturn('30');

        $this->assertSame(30, $this->service->getMaxAgeDays());
    }

    public function testNotificationDaysIs90Percent(): void {
        $this->config->method('getAppValue')
            ->willReturn('100');

        $this->assertSame(90, $this->service->getNotificationDays());
    }

    public function testNotificationDaysRoundsDown(): void {
        $this->config->method('getAppValue')
            ->willReturn('365');

        $this->assertSame(328, $this->service->getNotificationDays());
    }

    public function testNotificationDaysIsAtLeastOne(): void {
        $this->config->method('getAppValue')
            ->willReturn('1');

        $this->assertSame(1, $this->service->getNotificationDays());
    }

    public function testGetDeletionDateAddsDays(): void {
        $this->config->method('getAppValue')
            ->willReturn('30');

        $from = new DateTime('2026-01-01');
        $result = $this->service->getDeletionDate($from);

        $this->assertSame('2026-01-31', $result->format('Y-m-d'));
    }

    public function testGetDeletionDateDoesNotMutateInput(): void {
        $this->config->method('getAppValue')
            ->willReturn('30');

        $from = new DateTime('2026-01-01');
        $original = clone $from;
        $this->service->getDeletionDate($from);

        $this->assertSame($original->format('Y-m-d'), $from->format('Y-m-d'));
    }
}
