<?php

/**
 * PHPStan bootstrap — stubs for Laravel global helpers that are always
 * available at runtime in a Laravel application but are not present in the
 * standalone illuminate/* packages this package depends on.
 */

if (! function_exists('app')) {
    function app(string $abstract = null, array $parameters = []): mixed
    {
        return null;
    }
}

if (! function_exists('config')) {
    function config(string|null $key = null, mixed $default = null): mixed
    {
        return null;
    }
}

if (! function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return '';
    }
}

if (! function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return '';
    }
}

if (! function_exists('session')) {
    function session(string|array|null $key = null, mixed $default = null): mixed
    {
        return null;
    }
}
