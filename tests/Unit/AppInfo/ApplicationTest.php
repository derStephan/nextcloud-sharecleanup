<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\AppInfo;

use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Listener\ShareCreatedListener;
use OCA\ShareCleanup\Notification\Notifier;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Share\Events\ShareCreatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase {

    public function testAppIdConstant(): void {
        $this->assertSame('sharecleanup', Application::APP_ID);
    }

    public function testConstructorSetsAppId(): void {
        $app = new Application();
        $this->assertInstanceOf(Application::class, $app);
    }

    public function testConstructorWithUrlParams(): void {
        $app = new Application(['key' => 'value']);
        $this->assertInstanceOf(Application::class, $app);
    }

    public function testRegisterAddsEventListener(): void {
        $context = $this->createMock(IRegistrationContext::class);

        $context->expects($this->once())
            ->method('registerEventListener')
            ->with(ShareCreatedEvent::class, ShareCreatedListener::class);

        $context->expects($this->once())
            ->method('registerNotifierService')
            ->with(Notifier::class);

        $app = new Application();
        $app->register($context);
    }

    public function testBootDoesNothing(): void {
        $context = $this->createMock(IBootContext::class);

        $app = new Application();
        $app->boot($context);

        // No exception = success
        $this->assertTrue(true);
    }
}
