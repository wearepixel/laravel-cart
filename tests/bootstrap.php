<?php

require __DIR__ . '/../vendor/autoload.php';

// Suppress PHP 8.4 deprecation notices that originate from vendor code.
// These are typically from third-party packages (e.g. Mockery) that have not
// yet updated implicit-nullable parameter signatures and are not actionable here.
set_error_handler(static function (int $errno, string $errstr, string $errfile): bool {
    if ($errno === E_DEPRECATED && str_contains($errfile, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
        return true;
    }

    return false;
}, E_DEPRECATED);
