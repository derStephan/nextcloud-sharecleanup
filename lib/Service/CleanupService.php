<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Service;

use DateTime;
use OCA\ShareCleanup\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\IRootFolder;
use OCP\IConfig;
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
    ) {
    }

    public function isDryRun(): bool {
        return $this->config->getAppValue(
            Application::APP_ID,
            self::CONFIG_DRY_RUN,
            'yes'
        ) === 'yes';
    }

    /**
     * Discovers the share types that actually exist in the oc_share table.
     *
     * Because the query runs against the central share table, newly installed
     * apps (or newer Nextcloud versions) that introduce additional *file share
     * types* are picked up automatically — no code change needed here.
     *
     * This covers real *file shares* only (including Talk and Deck). App-internal
     * sharing that is not a file share (e.g. a Polls survey link) lives outside
     * the oc_share table and cannot be handled by any share-based app.
     *
     * Fallback is the list of IShare::TYPE_* constants of the running version.
     *
     * @return int[]
     */
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

        // Fallback: every IShare::TYPE_* constant of the running Nextcloud version.
        $types = [];
        foreach ((new \ReflectionClass(IShare::class))->getConstants() as $name => $value) {
            if (str_starts_with($name, 'TYPE_') && is_int($value)) {
                $types[] = $value;
            }
        }
        return $types;
    }

    /**
     * Sends advance notifications, ends expired shares and cleans up orphaned tags.
     *
     * Shares with their own expiration date are skipped entirely.
     *
     * @param int|null  $daysOverride    Override configured max age for this run
     * @param bool|null $dryRunOverride  Override configured dry-run mode for this run
     * @return array{scanned: int, skipped_expiry: int, notified: int, ended: int, tags_removed: int, failed: int}
     */
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
            // Phase 1: collect candidate shares WITHOUT touching anything,
            // so pagination offsets do not shift while we work.
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

                    // Never touch shares that carry their own expiration date.
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
                    break; // last page
                }
                $offset += $limit;
            }

            // Phase 2: act on the collected candidates.
            foreach ($candidates as $candidate) {
                $share = $candidate['share'];
                if ($candidate['action'] === 'end') {
                    $this->expireShare($share, $maxAgeDays, $dryRun, $stats);
                } else {
                    $this->notifyShare($share, $maxAgeDays, $dryRun, $stats);
                }
            }
        }

        // Remove all of our tags whose date lies in the past
        // (single mechanism — covers shares ended by this app or deleted manually).
        if (!$dryRun) {
            $stats['tags_removed'] = $this->tagService->cleanupPastTags();
        }

        $this->logger->info(
            'ShareCleanup: run finished (scanned: {s}, skipped (own expiry): {se}, notified: {n}, shares ended: {d}, tags removed: {tr}, failed: {f})',
            [
                's' => $stats['scanned'],
                'se' => $stats['skipped_expiry'],
                'n' => $stats['notified'],
                'd' => $stats['ended'],
                'tr' => $stats['tags_removed'],
                'f' => $stats['failed'],
                'app' => 'sharecleanup',
            ]
        );

        return $stats;
    }

    private function notifyShare(IShare $share, int $maxAgeDays, bool $dryRun, array &$stats): void {
        $shareId = $share->getId();
        $sharer = $share->getSharedBy();

        if (!$sharer || !$this->userManager->userExists($sharer)) {
            return;
        }

        // Send the advance notice exactly once per share.
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

            // Mark as notified BEFORE waiting for the next run (idempotency).
            $this->config->setAppValue(Application::APP_ID, self::PREFIX_NOTIFIED . $shareId, 'yes');

            $this->logger->info(
                'ShareCleanup: notified {user} about expiring share {id} of "{file}"',
                ['user' => $sharer, 'id' => $shareId, 'file' => $fileName, 'app' => 'sharecleanup']
            );
            $stats['notified']++;
        } catch (\Throwable $e) {
            $this->logger->error(
                'ShareCleanup: failed to notify {user} about share {id}: {msg}',
                ['user' => $sharer, 'id' => $shareId, 'msg' => $e->getMessage(), 'app' => 'sharecleanup']
            );
            $stats['failed']++;
        }
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

            // Clean up the notification marker for the removed share.
            $this->config->deleteAppValue(Application::APP_ID, self::PREFIX_NOTIFIED . $shareId);

            $this->logger->warning('ShareCleanup: ended ' . $desc, ['app' => 'sharecleanup']);
            $stats['ended']++;
        } catch (\Throwable $e) {
            $this->logger->error(
                'ShareCleanup: failed to end ' . $desc . ': ' . $e->getMessage(),
                ['app' => 'sharecleanup']
            );
            $stats['failed']++;
        }
    }

    /**
     * Resolves a human-readable name of the shared file/folder.
     * Falls back to the node id if the node can no longer be resolved.
     */
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
