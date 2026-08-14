<?php

declare(strict_types=1);

/**
 * Test bootstrap.
 *
 * Two run modes:
 *
 * 1) Standalone (fast, no Nextcloud needed) — uses composer autoload + OCP stubs.
 *    Run:  composer install && vendor/bin/phpunit
 *
 * 2) Inside a Nextcloud checkout (integration) — if tests/ is placed under
 *    nextcloud/apps/sharecleanup and the NC autoloader is available, real OCP
 *    interfaces are used. We detect that via the NC core autoloader.
 */

// Prefer the real Nextcloud autoloader when the app lives inside a server checkout.
$ncAutoload = __DIR__ . '/../../../autoload.php';
if (file_exists($ncAutoload)) {
    require_once $ncAutoload;
    define('SHARECLEANUP_NC_INTEGRATION', true);
    return;
}

// Standalone: use composer autoloader for our own classes.
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Provide minimal OCP stubs so the unit tests run without a full Nextcloud.
require_once __DIR__ . '/stubs.php';

define('SHARECLEANUP_NC_INTEGRATION', false);
