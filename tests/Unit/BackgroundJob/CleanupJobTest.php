<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\BackgroundJob;

use OCA\ShareCleanup\BackgroundJob\CleanupJob;
use OCA\ShareCleanup\Service\CleanupService;
use OCP\AppFramework\Utility\ITimeFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CleanupJobTest extends TestCase {

    private ITimeFactory&MockObject $timeFactory;
    private CleanupService&MockObject $cleanupService;
    private CleanupJob $job;

    protected function setUp(): void {
        parent::setUp();
        $this->timeFactory = $this->createMock(ITimeFactory::class);
        $this->cleanupService = $this->createMock(CleanupService::class);

        $this->job = new CleanupJob(
            $this->timeFactory,
            $this->cleanupService
        );
    }

    public function testJobCallsCleanupServiceRun(): void {
        $this->cleanupService->expects($this->once())
            ->method('run');

        // Use reflection to call the protected run() method
        $reflection = new \ReflectionClass($this->job);
        $method = $reflection->getMethod('run');
        $method->setAccessible(true);
        $method->invoke($this->job, null);
    }

    public function testJobIntervalIsOneHour(): void {
        $reflection = new \ReflectionClass($this->job);
        $property = $reflection->getProperty('interval');
        $property->setAccessible(true);

        $this->assertSame(3600, $property->getValue($this->job));
    }
}
