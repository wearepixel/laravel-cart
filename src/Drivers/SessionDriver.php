<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Drivers;

use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class SessionDriver implements CartDriver
{
    private string $itemsKey;

    private string $conditionsKey;

    public function __construct(
        private mixed $session,
        private string $sessionKey,
    ) {
        $this->itemsKey = $sessionKey . '_cart_items';
        $this->conditionsKey = $sessionKey . '_cart_conditions';
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey): void
    {
        $items = $this->session->get($this->itemsKey);
        $conditions = $this->session->get($this->conditionsKey);

        $this->session->forget($this->itemsKey);
        $this->session->forget($this->conditionsKey);

        $this->sessionKey = $sessionKey;
        $this->itemsKey = $sessionKey . '_cart_items';
        $this->conditionsKey = $sessionKey . '_cart_conditions';

        if ($items !== null) {
            $this->session->put($this->itemsKey, $items);
        }

        if ($conditions !== null) {
            $this->session->put($this->conditionsKey, $conditions);
        }
    }

    public function getDriverName(): string
    {
        return 'session';
    }

    public function getItems(): array
    {
        return $this->session->get($this->itemsKey) ?? [];
    }

    public function putItems(array $items): void
    {
        $this->session->put($this->itemsKey, $items);
    }

    public function getConditions(): array
    {
        return $this->session->get($this->conditionsKey) ?? [];
    }

    public function putConditions(array $conditions): void
    {
        $this->session->put($this->conditionsKey, $conditions);
    }

    public function clearItems(): void
    {
        $this->session->forget($this->itemsKey);
    }

    public function flush(): void
    {
        $this->session->forget($this->itemsKey);
        $this->session->forget($this->conditionsKey);
    }

    public function getSessionModel(): mixed
    {
        return null;
    }
}
