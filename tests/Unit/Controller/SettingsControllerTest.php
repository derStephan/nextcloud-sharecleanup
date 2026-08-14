<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Controller;

use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Controller\SettingsController;
use OCA\ShareCleanup\Service\CleanupService;
use OCA\ShareCleanup\Service\TagService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SettingsControllerTest extends TestCase {

    private IConfig&MockObject $config;
    private LoggerInterface&MockObject $logger;
    private IRequest&MockObject $request;
    private SettingsController $controller;

    protected function setUp(): void {
        parent::setUp();
        $this->config = $this->createMock(IConfig::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->request = $this->createMock(IRequest::class);

        $this->controller = new SettingsController(
            Application::APP_ID,
            $this->request,
            $this->config,
            $this->logger
        );
    }

    public function testSaveWithValidValues(): void {
        $this->config->expects($this->exactly(2))
            ->method('setAppValue')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    $this->assertSame(Application::APP_ID, $app);
                    $this->assertSame(TagService::CONFIG_DAYS, $key);
                    $this->assertSame('30', $value);
                } else {
                    $this->assertSame(Application::APP_ID, $app);
                    $this->assertSame(CleanupService::CONFIG_DRY_RUN, $key);
                    $this->assertSame('no', $value);
                }
            });

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('settings updated'),
                $this->arrayHasKey('days')
            );

        $response = $this->controller->save(30, false);

        $this->assertInstanceOf(JSONResponse::class, $response);
        $this->assertSame(['status' => 'ok'], $response->getData());
    }

    public function testSaveClampsMaxAgeToMinimum(): void {
        $this->config->expects($this->exactly(2))
            ->method('setAppValue')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    $this->assertSame('1', $value); // clamped to minimum
                }
            });

        $this->controller->save(0, true);
    }

    public function testSaveClampsMaxAgeToMaximum(): void {
        $this->config->expects($this->exactly(2))
            ->method('setAppValue')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    $this->assertSame('3650', $value); // clamped to maximum
                }
            });

        $this->controller->save(9999, true);
    }

    public function testSaveWithDryRunEnabled(): void {
        $this->config->expects($this->exactly(2))
            ->method('setAppValue')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                if ($calls === 2) {
                    $this->assertSame('yes', $value); // dry-run enabled
                }
            });

        $this->controller->save(365, true);
    }

    public function testSaveWithDryRunDisabled(): void {
        $this->config->expects($this->exactly(2))
            ->method('setAppValue')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                if ($calls === 2) {
                    $this->assertSame('no', $value); // dry-run disabled
                }
            });

        $this->controller->save(365, false);
    }
}
