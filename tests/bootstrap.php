<?php

declare(strict_types=1);

/**
 * Test bootstrap.
 *
 * Standalone mode: composer autoload + OCP stubs.
 * Integration mode: real Nextcloud autoloader (when placed in apps/ directory).
 */

// Prefer the real Nextcloud autoloader when the app lives inside a server checkout.
$ncAutoload = __DIR__ . '/../../../autoload.php';
if (file_exists($ncAutoload)) {
    require_once $ncAutoload;
    define('SHARECLEANUP_NC_INTEGRATION', true);
    return;
}

// Standalone: use composer autoloader for PHPUnit and our own classes.
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Provide minimal OCP stubs so the unit tests run without a full Nextcloud.
require_once __DIR__ . '/stubs.php';

// Manually load our app classes since we removed autoload from composer.json
// to avoid conflicts with OCP stubs.
spl_autoload_register(function (string $class): void {
    $prefix = 'OCA\\ShareCleanup\\';
    $baseDir = __DIR__ . '/../lib/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

define('SHARECLEANUP_NC_INTEGRATION', false);
