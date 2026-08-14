<?php

declare(strict_types=1);

/**
 * Minimal OCP stubs for standalone unit testing without a Nextcloud checkout.
 *
 * These are intentionally tiny: they only declare the interfaces/classes our
 * code type-hints against, so PHPUnit can mock them. They are NOT used when the
 * tests run inside a real Nextcloud (the real OCP interfaces win there).
 */

namespace OCP\AppFramework\Utility {
    interface ITimeFactory {
        public function getTime(): int;
    }
}

namespace OCP {
    interface IConfig {
        public function getAppValue(string $app, string $key, string $default = ''): string;
        public function setAppValue(string $app, string $key, string $value): void;
        public function deleteAppValue(string $app, string $key): void;
    }
    interface IUserManager {
        public function userExists(string $uid): bool;
    }
    interface IDBConnection {
        public function getQueryBuilder(): \OCP\DB\QueryBuilder\IQueryBuilder;
    }
}

namespace OCP\DB\QueryBuilder {
    interface IQueryBuilder {
        public const PARAM_INT = 1;
        public function selectDistinct(string $select): self;
        public function from(string $from): self;
        public function executeQuery(): \OCP\DB\IResult;
    }
}

namespace OCP\DB {
    interface IResult {
        public function fetch(): array|false;
        public function closeCursor(): bool;
    }
}

namespace OCP\Files {
    interface IRootFolder {
        public function getById(int $id): array;
    }
}

namespace OCP\Share {
    interface IShare {
        public const TYPE_USER = 0;
        public const TYPE_GROUP = 1;
        public const TYPE_LINK = 3;
        public const TYPE_EMAIL = 4;
        public const TYPE_REMOTE = 6;
        public const TYPE_CIRCLE = 7;
        public const TYPE_REMOTE_GROUP = 9;
        public const TYPE_ROOM = 10;
        public const TYPE_DECK = 12;
        public const TYPE_SCIENCEMESH = 15;
        public const TYPE_HOOK = 16;

        public function getId(): string;
        public function getShareType(): int;
        public function getNodeId(): int;
        public function getSharedBy(): string;
        public function getSharedWith();
        public function getShareTime(): ?\DateTime;
        public function getExpirationDate(): ?\DateTime;
        public function getNode();
    }
    interface IManager {
        public function getAllShares(int $shareType, bool $includeExpired = false, int $limit = 50, int $offset = 0): array;
        public function deleteShare(IShare $share): void;
    }
}

namespace OCP\Share\Events {
    class ShareCreatedEvent extends \OCP\EventDispatcher\Event {
        public function __construct(private \OCP\Share\IShare $share) { parent::__construct(); }
        public function getShare(): \OCP\Share\IShare { return $this->share; }
    }
}

namespace OCP\EventDispatcher {
    class Event {}
    interface IEventListener {
        public function handle(Event $event): void;
    }
}

namespace OCP\SystemTag {
    interface ISystemTag {
        public function getId(): string;
        public function getName(): string;
    }
    interface ISystemTagManager {
        public function matchingTagsByName(string $name): array;
        public function createTag(string $name, bool $userVisible, bool $userAssignable): ISystemTag;
        public function deleteTags(array $tagIds): void;
    }
    interface ISystemTagObjectMapper {
        public function getTagIdsForObjects(array $objectIds, string $objectType): array;
        public function getObjectIdsForTags(array $tagIds, string $objectType): array;
        public function assignTags(string $objectId, string $objectType, array $tagIds): void;
        public function unassignTags(string $objectId, string $objectType, array $tagIds): void;
    }
    class TagNotFoundException extends \Exception {}
}

namespace OCP\Notification {
    interface IManager {
        public function createNotification(): INotification;
        public function notify(INotification $notification): void;
    }
    interface INotification {
        public function setApp(string $app): self;
        public function setUser(string $user): self;
        public function setDateTime(\DateTime $dateTime): self;
        public function setObject(string $type, string $id): self;
        public function setSubject(string $subject, array $parameters = []): self;
        public function getApp(): string;
        public function getSubject(): string;
        public function getSubjectParameters(): array;
        public function setIcon(string $icon): self;
        public function setLink(string $link): self;
        public function setParsedSubject(string $subject): self;
        public function setParsedMessage(string $message): self;
    }
}

namespace Psr\Log {
    if (!interface_exists(LoggerInterface::class)) {
        interface LoggerInterface {
            public function info($message, array $context = []): void;
            public function warning($message, array $context = []): void;
            public function error($message, array $context = []): void;
        }
    }
}
