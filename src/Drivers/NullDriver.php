<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Drivers;

use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class NullDriver implements CartDriver
{
    private array $items = [];

    private array $conditions = [];

    public function __construct(private string $sessionKey) {}

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    public function getDriverName(): string
    {
        return 'null';
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function putItems(array $items): void
    {
        $this->items = $items;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function putConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function clearItems(): void
    {
        $this->items = [];
    }

    public function flush(): void
    {
        $this->items = [];
        $this->conditions = [];
    }

    public function getSessionModel(): mixed
    {
        return null;
    }
}
