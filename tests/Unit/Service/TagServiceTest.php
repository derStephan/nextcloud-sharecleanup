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

    private function mockDays(string $value): void {
        $this->config->method('getAppValue')
            ->willReturnCallback(fn($app, $key, $default = '') => $value);
    }

    public function testDefaultMaxAgeIs365(): void {
        $this->config->method('getAppValue')->willReturn('365');
        $this->assertSame(365, $this->service->getMaxAgeDays());
    }

    public function testMaxAgeFallsBackToDefaultWhenUnset(): void {
        // Return the default '' → cast to 0 → max(1, 0) = 1? No: default is '365' string.
        $this->config->method('getAppValue')
            ->willReturnCallback(fn($app, $key, $default = '') => $default);
        $this->assertSame(TagService::DEFAULT_DAYS, $this->service->getMaxAgeDays());
    }

    public function testMaxAgeIsAtLeastOne(): void {
        $this->mockDays('0');
        $this->assertSame(1, $this->service->getMaxAgeDays());
    }

    public function testCustomMaxAge(): void {
        $this->mockDays('90');
        $this->assertSame(90, $this->service->getMaxAgeDays());
    }

    public function testNotificationDaysIs90Percent(): void {
        $this->mockDays('365');
        // floor(365 * 0.9) = 328
        $this->assertSame(328, $this->service->getNotificationDays());
    }

    public function testNotificationDaysRoundsDown(): void {
        $this->mockDays('100');
        // floor(100 * 0.9) = 90
        $this->assertSame(90, $this->service->getNotificationDays());
    }

    public function testNotificationDaysIsAtLeastOne(): void {
        $this->mockDays('1');
        // floor(1 * 0.9) = 0 → max(1, 0) = 1
        $this->assertSame(1, $this->service->getNotificationDays());
    }

    public function testGetDeletionDateAddsDays(): void {
        $this->mockDays('365');
        $from = new DateTime('2026-08-14 12:00:00');
        $result = $this->service->getDeletionDate($from);
        $this->assertSame('2027-08-14', $result->format('Y-m-d'));
    }

    public function testGetDeletionDateDoesNotMutateInput(): void {
        $this->mockDays('30');
        $from = new DateTime('2026-01-01 00:00:00');
        $this->service->getDeletionDate($from);
        // Original must be unchanged (service clones before modify).
        $this->assertSame('2026-01-01', $from->format('Y-m-d'));
    }

    public function testTagNameForDate(): void {
        $date = new DateTime('2027-08-15');
        $this->assertSame('Share ends on 2027-08-15', $this->service->tagNameForDate($date));
    }

    public function testTagPrefixIsEnglish(): void {
        // Tags are fixed English because system tags are not translated per user.
        $this->assertSame('Share ends on ', TagService::TAG_PREFIX);
    }
}
