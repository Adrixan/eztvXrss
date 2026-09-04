<?php

declare(strict_types=1);

/**
 * Fallback PSR-4 autoloader for EztvXrss namespace when vendor/autoload.php is not present.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'EztvXrss\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
