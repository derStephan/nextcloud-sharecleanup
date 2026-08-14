<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Service;

use DateTime;
use OCA\ShareCleanup\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IUserManager;
use OCP\Notification\IManager as INotificationManager;
use OCP\Share\IManager as IShareManager;
use OCP\Share\IShare;
use Psr\Log\LoggerInterface;

class CleanupService {

    public const CONFIG_DRY_RUN = 'dry_run';
    public const PREFIX_NOTIFIED = 'notified_';

    public function __construct(
        private IShareManager $shareManager,
        private INotificationManager $notificationManager,
        private IConfig $config,
        private ITimeFactory $timeFactory,
        private IUserManager $userManager,
        private IRootFolder $rootFolder,
        private TagService $tagService,
        private LoggerInterface $logger,
        private IDBConnection $db,
    ) {
    }

    public function isDryRun(): bool {
        return $this->config->getAppValue(
            Application::APP_ID,
            self::CONFIG_DRY_RUN,
            'yes'
        ) === 'yes';
    }

    public function run(?int $daysOverride = null, ?bool $dryRunOverride = null): array {
        $maxAgeDays = $daysOverride !== null ? max(1, $daysOverride) : $this->tagService->getMaxAgeDays();
        $notifyDays = max(1, (int)floor($maxAgeDays * 0.9));
        $dryRun = $dryRunOverride ?? $this->isDryRun();

        $now = (new DateTime())->setTimestamp($this->timeFactory->getTime());
        $deleteCutoff = (clone $now)->modify('-' . $maxAgeDays . ' days');
        $notifyCutoff = (clone $now)->modify('-' . $notifyDays . ' days');

        $shareTypes = $this->discoverShareTypes();

        $this->logger->info(
            'ShareCleanup: starting run (max age: {days} days, notify after: {ndays} days (90 %), dry-run: {dry}, share types: [{types}])',
            [
                'days' => $maxAgeDays,
                'ndays' => $notifyDays,
                'dry' => $dryRun ? 'yes' : 'no',
                'types' => implode(', ', $shareTypes),
                'app' => 'sharecleanup',
            ]
        );

        $stats = ['scanned' => 0, 'skipped_expiry' => 0, 'notified' => 0, 'ended' => 0, 'tags_removed' => 0, 'failed' => 0];

        foreach ($shareTypes as $type) {
            $candidates = [];

            $offset = 0;
            $limit = 100;
            while (true) {
                try {
                    $shares = $this->shareManager->getAllShares($type, true, $limit, $offset);
                } catch (\Throwable $e) {
                    $this->logger->warning(
                        'ShareCleanup: failed to list shares of type {type}: {msg}',
                        ['type' => $type, 'msg' => $e->getMessage(), 'app' => 'sharecleanup']
                    );
                    break;
                }

                if ($shares instanceof \Traversable) {
                    $shares = iterator_to_array($shares);
                }

                if (!is_array($shares) || count($shares) === 0) {
                    break;
                }

                foreach ($shares as $share) {
                    $stats['scanned']++;

                    if ($share->getExpirationDate() !== null) {
                        $stats['skipped_expiry']++;
                        continue;
                    }

                    $stime = $share->getShareTime();
                    if ($stime === null) {
                        continue;
                    }

                    if ($stime <= $deleteCutoff) {
                        $candidates[] = ['share' => $share, 'action' => 'end'];
                    } elseif ($stime <= $notifyCutoff) {
                        $candidates[] = ['share' => $share, 'action' => 'notify'];
                    }
                }

                if (count($shares) < $limit) {
                    break;
                }
                $offset += $limit;
            }

            foreach ($candidates as $candidate) {
                $share = $candidate['share'];
                if ($candidate['action'] === 'end') {
                    $this->expireShare($share, $maxAgeDays, $dryRun, $stats);
                } else {
                    $this->notifyShare($share, $maxAgeDays, $dryRun, $stats);
                }
            }
        }

        $stats['tags_removed'] = $this->tagService->cleanupPastTags();

        $this->logger->info(
            'ShareCleanup: run finished (scanned: {scanned}, skipped expiry: {skipped}, notified: {notified}, ended: {ended}, tags removed: {tags}, failed: {failed})',
            [
                'scanned' => $stats['scanned'],
                'skipped' => $stats['skipped_expiry'],
                'notified' => $stats['notified'],
                'ended' => $stats['ended'],
                'tags' => $stats['tags_removed'],
                'failed' => $stats['failed'],
                'app' => 'sharecleanup',
            ]
        );

        return $stats;
    }

    public function discoverShareTypes(): array {
        try {
            $qb = $this->db->getQueryBuilder();
            $qb->selectDistinct('share_type')->from('share');
            $result = $qb->executeQuery();

            $types = [];
            while (($row = $result->fetch()) !== false) {
                $types[] = (int)$row['share_type'];
            }
            $result->closeCursor();

            if (count($types) > 0) {
                return $types;
            }
        } catch (\Throwable $e) {
            $this->logger->warning(
                'ShareCleanup: could not discover share types from database ({msg}); falling back to known types',
                ['msg' => $e->getMessage(), 'app' => 'sharecleanup']
            );
        }

        $types = [];
        foreach ((new \ReflectionClass(IShare::class))->getConstants() as $name => $value) {
            if (str_starts_with($name, 'TYPE_') && is_int($value)) {
                $types[] = $value;
            }
        }
        return $types;
    }

    private function expireShare(IShare $share, int $maxAgeDays, bool $dryRun, array &$stats): void {
        $shareId = $share->getId();
        $fileName = $this->resolveFileName($share);

        $desc = sprintf(
            'share id=%s type=%d file="%s" sharedBy=%s created=%s',
            $shareId,
            $share->getShareType(),
            $fileName,
            $share->getSharedBy(),
            $share->getShareTime()->format(DateTime::ATOM)
        );

        if ($dryRun) {
            $this->logger->warning(
                'ShareCleanup [dry-run]: would end ' . $desc,
                ['app' => 'sharecleanup']
            );
            $stats['ended']++;
            return;
        }

        try {
            $this->shareManager->deleteShare($share);
            $this->config->deleteAppValue(Application::APP_ID, self::PREFIX_NOTIFIED . $shareId);
            $this->logger->warning('ShareCleanup: ended ' . $desc, ['app' => 'sharecleanup']);
            $stats['ended']++;
        } catch (\Throwable $e) {
            $this->logger->error(
                'ShareCleanup: failed to end share {id}: {msg}',
                ['id' => $shareId, 'msg' => $e->getMessage(), 'app' => 'sharecleanup']
            );
            $stats['failed']++;
        }
    }

    private function notifyShare(IShare $share, int $maxAgeDays, bool $dryRun, array &$stats): void {
        $shareId = $share->getId();
        $sharer = $share->getSharedBy();

        if (!$sharer || !$this->userManager->userExists($sharer)) {
            return;
        }

        if ($this->config->getAppValue(Application::APP_ID, self::PREFIX_NOTIFIED . $shareId, '') === 'yes') {
            return;
        }

        $endDate = (clone $share->getShareTime())->modify('+' . $maxAgeDays . ' days');
        $fileName = $this->resolveFileName($share);

        if ($dryRun) {
            $this->logger->info(
                'ShareCleanup [dry-run]: would notify {user} about expiring share {id} of "{file}" (share ends on {date})',
                [
                    'user' => $sharer,
                    'id' => $shareId,
                    'file' => $fileName,
                    'date' => $endDate->format('Y-m-d'),
                    'app' => 'sharecleanup',
                ]
            );
            $stats['notified']++;
            return;
        }

        try {
            $notification = $this->notificationManager->createNotification();
            $notification
                ->setApp(Application::APP_ID)
                ->setUser($sharer)
                ->setDateTime(new DateTime())
                ->setObject('share', (string)$shareId)
                ->setSubject('expiring_share', [
                    'file' => $fileName,
                    'date' => $endDate->format('Y-m-d'),
                ]);

            $this->notificationManager->notify($notification);
            $this->config->setAppValue(Application::APP_ID, self::PREFIX_NOTIFIED . $shareId, 'yes');

            $this->logger->info(
                'ShareCleanup: notified {user} about expiring share {id} of "{file}"',
                ['user' => $sharer, 'id' => $shareId, 'file' => $fileName, 'app' => 'sharecleanup']
            );
            $stats['notified']++;
        } catch (\Throwable $e) {
            $this->logger->error(
                'ShareCleanup: failed to notify about share {id}: {msg}',
                ['id' => $shareId, 'msg' => $e->getMessage(), 'app' => 'sharecleanup']
            );
            $stats['failed']++;
        }
    }

    private function resolveFileName(IShare $share): string {
        try {
            $node = $share->getNode();
            return $node->getName();
        } catch (\Throwable) {
            try {
                $nodes = $this->rootFolder->getById($share->getNodeId());
                if (count($nodes) > 0) {
                    return $nodes[0]->getName();
                }
            } catch (\Throwable) {
                // ignore
            }
        }
        return '#' . $share->getNodeId();
    }
}
