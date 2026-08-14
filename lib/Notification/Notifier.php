<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Notification;

use OCA\ShareCleanup\AppInfo\Application;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\IURLGenerator;

class Notifier implements INotifier {

    public function __construct(
        private IFactory $l10nFactory,
        private IURLGenerator $urlGenerator,
    ) {
    }

    public function getID(): string {
        return Application::APP_ID;
    }

    public function getName(): string {
        return 'Share Cleanup';
    }

    public function prepare(INotification $notification, string $languageCode): INotification {
        if ($notification->getApp() !== Application::APP_ID) {
            throw new \InvalidArgumentException('Unknown app');
        }

        $l = $this->l10nFactory->get(Application::APP_ID, $languageCode);

        $params = $notification->getSubjectParameters();
        $file = $params['file'] ?? '';
        $date = $params['date'] ?? '';

        $notification
            ->setIcon($this->urlGenerator->imagePath(Application::APP_ID, 'icon.svg'))
            ->setLink($this->urlGenerator->linkToRouteAbsolute('files.view.index'))
            ->setParsedSubject($l->t('Share of "%s" ends soon', [$file]))
            ->setParsedMessage($l->t(
                'Your share of "%1$s" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.',
                [$file, $date]
            ));

        return $notification;
    }
}
