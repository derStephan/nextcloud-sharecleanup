<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Tests\Unit\Notification;

use OCA\ShareCleanup\AppInfo\Application;
use OCA\ShareCleanup\Notification\Notifier;
use OCP\L10N\IFactory;
use OCP\L10N\IL10N;
use OCP\Notification\INotification;
use OCP\IURLGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotifierTest extends TestCase {

    private IFactory&MockObject $l10nFactory;
    private IURLGenerator&MockObject $urlGenerator;
    private Notifier $notifier;

    protected function setUp(): void {
        parent::setUp();
        $this->l10nFactory = $this->createMock(IFactory::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);

        $this->notifier = new Notifier(
            $this->l10nFactory,
            $this->urlGenerator
        );
    }

    public function testGetID(): void {
        $this->assertSame(Application::APP_ID, $this->notifier->getID());
    }

    public function testGetName(): void {
        $this->assertSame('Share Cleanup', $this->notifier->getName());
    }

    public function testPrepareThrowsForUnknownApp(): void {
        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn('otherapp');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown app');

        $this->notifier->prepare($notification, 'en');
    }

    public function testPrepareSetsCorrectData(): void {
        $l10n = $this->createMock(IL10N::class);
        $l10n->method('t')
            ->willReturnCallback(function ($text, $params = []) {
                return vsprintf($text, $params);
            });

        $this->l10nFactory->method('get')
            ->with(Application::APP_ID, 'de')
            ->willReturn($l10n);

        $this->urlGenerator->method('imagePath')
            ->with(Application::APP_ID, 'icon.svg')
            ->willReturn('/apps/sharecleanup/img/icon.svg');

        $this->urlGenerator->method('linkToRouteAbsolute')
            ->with('files.view.index')
            ->willReturn('https://cloud.example.com/apps/files/');

        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn(Application::APP_ID);
        $notification->method('getSubjectParameters')->willReturn([
            'file' => 'document.pdf',
            'date' => '2026-09-15',
        ]);

        $notification->expects($this->once())
            ->method('setIcon')
            ->with('/apps/sharecleanup/img/icon.svg')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setLink')
            ->with('https://cloud.example.com/apps/files/')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setParsedSubject')
            ->with('Share of "document.pdf" ends soon')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setParsedMessage')
            ->with($this->stringContains('document.pdf'))
            ->willReturnSelf();

        $result = $this->notifier->prepare($notification, 'de');

        $this->assertSame($notification, $result);
    }

    public function testPrepareHandlesMissingParameters(): void {
        $l10n = $this->createMock(IL10N::class);
        $l10n->method('t')
            ->willReturnCallback(function ($text, $params = []) {
                // Handle both %s and %1$s style placeholders
                if (empty($params)) {
                    return $text;
                }
                return vsprintf($text, $params);
            });

        $this->l10nFactory->method('get')->willReturn($l10n);
        $this->urlGenerator->method('imagePath')->willReturn('/icon.svg');
        $this->urlGenerator->method('linkToRouteAbsolute')->willReturn('/files');

        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn(Application::APP_ID);
        $notification->method('getSubjectParameters')->willReturn([]);

        $notification->expects($this->once())
            ->method('setIcon')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setLink')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setParsedSubject')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setParsedMessage')
            ->willReturnSelf();

        $result = $this->notifier->prepare($notification, 'en');

        $this->assertSame($notification, $result);
    }
}
