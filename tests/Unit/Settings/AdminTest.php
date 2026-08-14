<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Settings;

use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Settings\Admin;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase {

    private IConfig&MockObject $config;
    private IInitialState&MockObject $initialState;
    private Admin $admin;

    protected function setUp(): void {
        parent::setUp();
        $this->config = $this->createMock(IConfig::class);
        $this->initialState = $this->createMock(IInitialState::class);

        $this->admin = new Admin(
            $this->config,
            $this->initialState
        );
    }

    public function testGetSectionReturnsSharing(): void {
        $this->assertSame('sharing', $this->admin->getSection());
    }

    public function testGetPriorityReturns50(): void {
        $this->assertSame(50, $this->admin->getPriority());
    }

    public function testGetFormProvidesInitialState(): void {
        $this->config->method('getAppValue')
            ->willReturnCallback(function ($app, $key, $default = '') {
                if ($key === 'max_age_days') return '180';
                if ($key === 'dry_run') return 'no';
                return $default;
            });

        $this->initialState->expects($this->exactly(2))
            ->method('provideInitialState')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                $this->assertSame(Application::APP_ID, $app);
                if ($calls === 1) {
                    $this->assertSame('max_age_days', $key);
                    $this->assertSame(180, $value);
                } else {
                    $this->assertSame('dry_run', $key);
                    $this->assertFalse($value);
                }
            });

        $response = $this->admin->getForm();

        $this->assertInstanceOf(TemplateResponse::class, $response);
    }

    public function testGetFormUsesDefaultsWhenConfigEmpty(): void {
        $this->config->method('getAppValue')
            ->willReturnCallback(fn($app, $key, $default = '') => $default);

        $this->initialState->expects($this->exactly(2))
            ->method('provideInitialState')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    $this->assertSame('max_age_days', $key);
                    $this->assertSame(365, $value); // default
                } else {
                    $this->assertSame('dry_run', $key);
                    $this->assertTrue($value); // default 'yes'
                }
            });

        $this->admin->getForm();
    }

    public function testGetFormDryRunEnabled(): void {
        $this->config->method('getAppValue')
            ->willReturnCallback(function ($app, $key, $default = '') {
                if ($key === 'dry_run') return 'yes';
                return $default;
            });

        $this->initialState->expects($this->exactly(2))
            ->method('provideInitialState')
            ->willReturnCallback(function ($app, $key, $value) {
                static $calls = 0;
                $calls++;
                if ($calls === 2) {
                    $this->assertTrue($value); // dry_run = true
                }
            });

        $this->admin->getForm();
    }
}
