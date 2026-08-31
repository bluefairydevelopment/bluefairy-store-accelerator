<?php
declare(strict_types=1);

require_once __DIR__ . '/stubs.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'BlueFairy\\StoreAccelerator\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $file = __DIR__ . '/../includes/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
