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
        return max(1, (int)floor($this->getMaxAgeDays() * 0.9));
    }

    public function getDeletionDate(DateTime $from): DateTime {
        return (clone $from)->modify('+' . $this->getMaxAgeDays() . ' days');
    }

    public function tagNameForDate(DateTime $date): string {
        return self::TAG_PREFIX . $date->format('Y-m-d');
    }

    public function tagFileForShare(IShare $share): void {
        $node = $share->getNode();
        $shareTime = $share->getShareTime();
        if ($shareTime === null) {
            return;
        }

        $endDate = $this->getDeletionDate($shareTime);
        $tagName = $this->tagNameForDate($endDate);

        $tag = $this->getOrCreateTag($tagName);
        $this->tagMapper->assignTags((string)$node->getId(), 'files', [$tag->getId()]);

        $this->logger->info(
            'ShareCleanup: tagged file {file} with "{tag}"',
            ['file' => $node->getName(), 'tag' => $tagName, 'app' => 'sharecleanup']
        );
    }

    public function cleanupPastTags(): int {
        $now = (new DateTime())->setTimestamp($this->timeFactory->getTime());
        $removed = 0;

        $allTags = $this->tagManager->matchingTagsByName(self::TAG_PREFIX . '%');
        foreach ($allTags as $tag) {
            $name = $tag->getName();
            if (!str_starts_with($name, self::TAG_PREFIX)) {
                continue;
            }

            $dateStr = substr($name, strlen(self::TAG_PREFIX));
            try {
                $tagDate = new DateTime($dateStr);
            } catch (\Throwable) {
                continue;
            }

            if ($tagDate < $now) {
                $objectIds = $this->tagMapper->getObjectIdsForTags([$tag->getId()], 'files');
                if (count($objectIds) === 0) {
                    $this->tagManager->deleteTags([$tag->getId()]);
                    $removed++;
                    $this->logger->info(
                        'ShareCleanup: removed past tag "{tag}"',
                        ['tag' => $name, 'app' => 'sharecleanup']
                    );
                }
            }
        }

        return $removed;
    }

    private function getOrCreateTag(string $name): ISystemTag {
        $tags = $this->tagManager->matchingTagsByName($name);
        foreach ($tags as $tag) {
            if ($tag->getName() === $name) {
                return $tag;
            }
        }

        return $this->tagManager->createTag($name, false, false);
    }
}
