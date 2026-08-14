# Running the tests

This app ships **unit tests** for its core logic (PHPUnit).

## Two ways to run them

### Option A — Standalone (recommended for development)

No Nextcloud installation needed. The tests use minimal OCP stubs
(`tests/stubs.php`) so they run anywhere PHP is available.

```bash
cd apps/sharecleanup          # or wherever the app lives
composer install              # installs PHPUnit into vendor/
vendor/bin/phpunit            # runs the whole suite
```

Run a single test file or method:

```bash
vendor/bin/phpunit tests/Unit/Service/TagServiceTest.php
vendor/bin/phpunit --filter testNotificationDaysIs90Percent
```

With a coverage report (requires `pcov` or `xdebug`):

```bash
vendor/bin/phpunit --coverage-html coverage/
```

### Option B — Inside a Nextcloud checkout (integration)

If the app is placed at `nextcloud/apps/sharecleanup`, the test bootstrap
detects the real Nextcloud autoloader and uses the **real OCP interfaces**
instead of the stubs. Just run:

```bash
cd nextcloud/apps/sharecleanup
composer install
vendor/bin/phpunit
```

## What is covered

| Test class | What it verifies |
|---|---|
| `TagServiceTest` | Default 365 days, min 1 day, 90 % notification window, deletion-date math, English tag name, input not mutated |
| `CleanupServiceTest` | Dry-run default, **shares with own expiration are skipped**, expired shares ended (live) vs. counted (dry-run), young shares untouched, notification window |
| `ShareCreatedListenerTest` | Tags shares without own expiry, **skips shares with own expiry**, tagging errors are caught and logged (never break share creation) |

The most valuable tests are the **expiration-date protection** ones — they guard
the rule that shares carrying their own expiration date are never touched.

## Requirements

- PHP ≥ 8.1 with Composer
- `composer install` once to fetch PHPUnit

## Note

These tests were generated with the app by an AI assistant (Kimi K3, Moonshot AI).
They are statically reviewed but **should be executed once on your machine**
(`composer install && vendor/bin/phpunit`) to confirm they pass in your
environment before relying on them in CI.
