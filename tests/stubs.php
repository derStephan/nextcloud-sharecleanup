<?php

declare(strict_types=1);

/**
 * Minimal OCP stubs for standalone unit testing without a Nextcloud checkout.
 */

namespace OCP\AppFramework {
    class App {
        public function __construct(string $appName, array $urlParams = []) {}
    }
}

namespace OCP\AppFramework\Utility {
    interface ITimeFactory {
        public function getTime(): int;
    }
}

namespace OCP\AppFramework\Bootstrap {
    interface IBootstrap {}
    interface IBootContext {}
    interface IRegistrationContext {
        public function registerEventListener(string $event, string $listener): void;
        public function registerNotifierService(string $notifier): void;
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
        private \OCP\Share\IShare $share;
        public function __construct(\OCP\Share\IShare $share) {
            $this->share = $share;
        }
        public function getShare(): \OCP\Share\IShare {
            return $this->share;
        }
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



namespace OCP\AppFramework\Utility {
    interface ITimeFactory {
        public function getTime(): int;
    }
}

namespace OCP\BackgroundJob {
    abstract class TimedJob {
        protected int $interval = 0;
        public function __construct(ITimeFactory $time) {}
        protected function setInterval(int $seconds): void {
            $this->interval = $seconds;
        }
        abstract protected function run($argument): void;
    }
}

namespace OCP\L10N {
    interface IFactory {
        public function get(string $app, string $lang = null): IL10N;
    }
    interface IL10N {
        public function t(string $text, array $parameters = []): string;
    }
}

namespace OCP {
    interface IURLGenerator {
        public function imagePath(string $app, string $file): string;
        public function linkToRouteAbsolute(string $route): string;
    }
    interface IRequest {
        public function getParam(string $key, $default = null);
    }
}

namespace OCP\AppFramework\Http {
    class JSONResponse {
        public function __construct(private array $data = []) {}
        public function getData(): array {
            return $this->data;
        }
    }
}

namespace Symfony\Component\Console\Command {
    abstract class Command {
        protected ?string $name = null;
        public function __construct() {}
        public function setName(string $name): static {
            $this->name = $name;
            return $this;
        }
        public function getName(): ?string {
            return $this->name;
        }
        public function setDescription(string $description): static {
            return $this;
        }
        public function addOption(string $name, $shortcut = null, int $mode = 0, string $description = '', $default = null): static {
            return $this;
        }
        public function getDefinition(): array {
            return ['days' => true, 'dry-run' => true, 'force' => true];
        }
        abstract protected function configure(): void;
        abstract protected function execute(InputInterface $input, OutputInterface $output): int;
    }
}

namespace Symfony\Component\Console\Input {
    interface InputInterface {
        public function getOption(string $name);
    }
    class InputOption {
        public const VALUE_NONE = 1;
        public const VALUE_REQUIRED = 2;
        public const VALUE_OPTIONAL = 4;
        public const VALUE_IS_ARRAY = 8;
    }
}

namespace Symfony\Component\Console\Output {
    interface OutputInterface {
        public function writeln($messages, int $options = 0): void;
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
