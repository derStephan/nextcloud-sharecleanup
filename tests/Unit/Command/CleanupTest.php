<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Command;

use OCA\ShareCleanup\Command\Cleanup;
use OCA\ShareCleanup\Service\CleanupService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommandTest extends TestCase {

    private CleanupService&MockObject $cleanupService;
    private Cleanup $command;

    protected function setUp(): void {
        parent::setUp();
        $this->cleanupService = $this->createMock(CleanupService::class);
        $this->command = new Cleanup($this->cleanupService);
    }

    public function testCommandName(): void {
        $this->assertSame('sharecleanup:run', $this->command->getName());
    }

    public function testCommandHasOptions(): void {
        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasOption('days'));
        $this->assertTrue($definition->hasOption('dry-run'));
        $this->assertTrue($definition->hasOption('force'));
    }

    public function testExecuteWithDefaults(): void {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getOption')
            ->willReturnCallback(function ($name) {
                return match($name) {
                    'days' => null,
                    'dry-run' => false,
                    'force' => false,
                    default => null,
                };
            });

        $this->cleanupService->expects($this->once())
            ->method('run')
            ->with(null, null)
            ->willReturn([
                'scanned' => 10,
                'skipped_expiry' => 2,
                'notified' => 3,
                'ended' => 4,
                'failed' => 1,
            ]);

        $output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Scanned: 10'));

        $result = $this->command->execute($input, $output);
        $this->assertSame(0, $result);
    }

    public function testExecuteWithDaysOverride(): void {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getOption')
            ->willReturnCallback(function ($name) {
                return match($name) {
                    'days' => '30',
                    'dry-run' => false,
                    'force' => false,
                    default => null,
                };
            });

        $this->cleanupService->expects($this->once())
            ->method('run')
            ->with(30, null)
            ->willReturn([
                'scanned' => 5,
                'skipped_expiry' => 0,
                'notified' => 1,
                'ended' => 2,
                'failed' => 0,
            ]);

        $result = $this->command->execute($input, $output);
        $this->assertSame(0, $result);
    }

    public function testExecuteWithDryRun(): void {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getOption')
            ->willReturnCallback(function ($name) {
                return match($name) {
                    'days' => null,
                    'dry-run' => true,
                    'force' => false,
                    default => null,
                };
            });

        $this->cleanupService->expects($this->once())
            ->method('run')
            ->with(null, true)
            ->willReturn([
                'scanned' => 3,
                'skipped_expiry' => 0,
                'notified' => 0,
                'ended' => 0,
                'failed' => 0,
            ]);

        $result = $this->command->execute($input, $output);
        $this->assertSame(0, $result);
    }

    public function testExecuteWithForce(): void {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getOption')
            ->willReturnCallback(function ($name) {
                return match($name) {
                    'days' => null,
                    'dry-run' => false,
                    'force' => true,
                    default => null,
                };
            });

        $this->cleanupService->expects($this->once())
            ->method('run')
            ->with(null, false)
            ->willReturn([
                'scanned' => 7,
                'skipped_expiry' => 1,
                'notified' => 2,
                'ended' => 3,
                'failed' => 0,
            ]);

        $result = $this->command->execute($input, $output);
        $this->assertSame(0, $result);
    }

    public function testExecuteWithDaysAndForce(): void {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getOption')
            ->willReturnCallback(function ($name) {
                return match($name) {
                    'days' => '90',
                    'dry-run' => false,
                    'force' => true,
                    default => null,
                };
            });

        $this->cleanupService->expects($this->once())
            ->method('run')
            ->with(90, false)
            ->willReturn([
                'scanned' => 20,
                'skipped_expiry' => 5,
                'notified' => 5,
                'ended' => 10,
                'failed' => 0,
            ]);

        $result = $this->command->execute($input, $output);
        $this->assertSame(0, $result);
    }
}
