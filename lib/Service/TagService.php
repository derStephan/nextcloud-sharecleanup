<?php

declare(strict_types=1);

namespace OCA\ShareCleanup\Service;

use DateTime;
use OCA\ShareCleanup\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\Share\IShare;
use OCP\SystemTag\ISystemTag;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use OCP\SystemTag\TagNotFoundException;
use Psr\Log\LoggerInterface;

class TagService {

    public const CONFIG_DAYS = 'max_age_days';
    public const DEFAULT_DAYS = 365;

    /**
     * Tag name: fixed English, because system tags are NOT translated per user —
     * they are a single stored string visible to everyone identically.
     * Wording: only the *share* ends — the data itself is NOT deleted.
     */
    public const TAG_PREFIX = 'Share ends on ';

    public function __construct(
        private IConfig $config,
        private ISystemTagManager $tagManager,
        private ISystemTagObjectMapper $tagMapper,
        private ITimeFactory $timeFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function getMaxAgeDays(): int {
        $days = (int)$this->config->getAppValue(
            Application::APP_ID,
            self::CONFIG_DAYS,
            (string)self::DEFAULT_DAYS
        );
        return max(1, $days);
    }

    public function getNotificationDays(): int {
        // Notify at 90 % of the lifetime, i.e. 10 % of the period before the share ends.
        return max(1, (int)floor($this->getMaxAgeDays() * 0.9));
    }

    public function getDeletionDate(DateTime $from): DateTime {
        return (clone $from)->modify('+' . $this->getMaxAgeDays() . ' days');
    }

    public function tagNameForDate(DateTime $date): string {
        return self::TAG_PREFIX . $date->format('Y-m-d');
    }

    /**
     * Finds an existing tag by exact name, or creates it.
     */
    private function getOrCreateTag(string $name): ISystemTag {
        $existing = $this->tagManager->matchingTagsByName($name);
        foreach ($existing as $tag) {
            if ($tag->getName() === $name) {
                return $tag;
            }
        }
        // Visible to all users, not user-assignable (managed solely by this app).
        return $this->tagManager->createTag($name, true, false);
    }

    /**
     * Attaches the "Share ends on …" tag for this share's end date to the file.
     * Multiple shares with the same end date share one tag;
     * different end dates produce additional date tags on the same file.
     */
    public function tagFileForShare(IShare $share): void {
        $shareTime = $share->getShareTime();
        if ($shareTime === null) {
            return;
        }

        $name = $this->tagNameForDate($this->getDeletionDate($shareTime));
        $tag = $this->getOrCreateTag($name);
        $fileId = (string)$share->getNodeId();

        $already = $this->tagMapper->getTagIdsForObjects([$fileId], 'files');
        $existingIds = $already[$fileId] ?? [];
        if (in_array($tag->getId(), $existingIds, true)) {
            return; // tag already on the file
        }

        $this->tagMapper->assignTags($fileId, 'files', [$tag->getId()]);

        $this->logger->info(
            'ShareCleanup: tagged file {fileId} with "{tag}"',
            ['fileId' => $fileId, 'tag' => $name, 'app' => 'sharecleanup']
        );
    }

    /**
     * Removes every one of our tags whose date lies in the past.
     *
     * This is the single, simple cleanup mechanism: a tag that has become
     * meaningless — whether because the share was ended by this app, deleted
     * manually, or otherwise — disappears on the next run once its date has
     * passed. No share-table lookups, no extra listeners.
     *
     * @return int number of tags deleted
     */
    public function cleanupPastTags(): int {
        $now = (new DateTime())->setTimestamp($this->timeFactory->getTime());
        $today = $now->format('Y-m-d');
        $deleted = 0;

        $candidates = $this->tagManager->matchingTagsByName(self::TAG_PREFIX);
        foreach ($candidates as $tag) {
            $name = $tag->getName();
            if (!str_starts_with($name, self::TAG_PREFIX)) {
                continue;
            }

            $date = substr($name, strlen(self::TAG_PREFIX));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }
            if ($date >= $today) {
                continue; // not in the past — keep
            }

            // Deleting a system tag automatically removes all its assignments.
            try {
                $this->tagManager->deleteTags([$tag->getId()]);
                $this->logger->info(
                    'ShareCleanup: deleted past tag "{tag}"',
                    ['tag' => $name, 'app' => 'sharecleanup']
                );
                $deleted++;
            } catch (TagNotFoundException) {
                // already gone — fine
            }
        }

        return $deleted;
    }
}
