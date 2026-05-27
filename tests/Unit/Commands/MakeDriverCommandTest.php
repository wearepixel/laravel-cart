<?php

use Wearepixel\Cart\Commands\MakeDriverCommand;

describe('MakeDriverCommand', function () {
    test('stub contains the given class name', function () {
        $stub = MakeDriverCommand::stub('RedisDriver');
        expect($stub)->toContain('class RedisDriver implements CartDriver');
    });

    test('stub uses the App\\Cart\\Drivers namespace', function () {
        $stub = MakeDriverCommand::stub('RedisDriver');
        expect($stub)->toContain('namespace App\\Cart\\Drivers');
    });

    test('stub imports the CartDriver interface', function () {
        $stub = MakeDriverCommand::stub('RedisDriver');
        expect($stub)->toContain('use Wearepixel\\Cart\\Drivers\\Contracts\\CartDriver');
    });

    test('stub declares all CartDriver interface methods', function () {
        $stub = MakeDriverCommand::stub('RedisDriver');
        expect($stub)->toContain('public function getSessionKey(): string');
        expect($stub)->toContain('public function setSessionKey(string $sessionKey): void');
        expect($stub)->toContain('public function getDriverName(): string');
        expect($stub)->toContain('public function getItems(): array');
        expect($stub)->toContain('public function putItems(array $items): void');
        expect($stub)->toContain('public function getConditions(): array');
        expect($stub)->toContain('public function putConditions(array $conditions): void');
        expect($stub)->toContain('public function clearItems(): void');
        expect($stub)->toContain('public function flush(): void');
        expect($stub)->toContain('public function getSessionModel(): mixed');
    });
});
