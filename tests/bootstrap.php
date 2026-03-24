<?php

use Composer\Autoload\ClassLoader;

/**
 * Worktree bootstrap: ensure this worktree's src/ takes precedence
 * over the shared vendor's autoload mapping (which may point to the
 * main branch or another worktree).
 *
 * We load the standard Composer autoloader first, then update the
 * ClassLoader's PSR-4 map so CCK\FilamentShot\ resolves from this
 * worktree's src/ directory.
 */
$_vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (! file_exists($_vendorAutoload)) {
    // When invoked via a shared vendor (e.g. main project's pest binary), fall back
    // to the nearest parent that has a vendor directory.
    foreach ([dirname(__DIR__, 2), dirname(__DIR__, 3)] as $_candidate) {
        if (file_exists($_candidate . '/vendor/autoload.php')) {
            $_vendorAutoload = $_candidate . '/vendor/autoload.php';
            break;
        }
    }
}
require $_vendorAutoload;

$_worktreeRoot = dirname(__DIR__);
$_loaders = spl_autoload_functions();

foreach ($_loaders as $_loader) {
    if (is_array($_loader) && isset($_loader[0]) && $_loader[0] instanceof ClassLoader) {
        // Override the PSR-4 mapping for the package namespace to use
        // this worktree's src directory. Keep Tests\ mapped to tests/.
        $_loader[0]->setPsr4('CCK\\FilamentShot\\', [$_worktreeRoot . '/src/']);
        $_loader[0]->setPsr4('CCK\\FilamentShot\\Tests\\', [$_worktreeRoot . '/tests/']);

        break;
    }
}

unset($_worktreeRoot, $_loaders, $_loader);
