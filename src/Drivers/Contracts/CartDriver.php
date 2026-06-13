<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Drivers\Contracts;

interface CartDriver
{
    public function getSessionKey(): string;

    public function setSessionKey(string $sessionKey): void;

    public function getDriverName(): string;

    public function getItems(): array;

    public function putItems(array $items): void;

    public function getConditions(): array;

    public function putConditions(array $conditions): void;

    public function clearItems(): void;

    public function flush(): void;

    public function getSessionModel(): mixed;
}
