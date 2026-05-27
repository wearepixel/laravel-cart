<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Drivers;

use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class RedisDriver implements CartDriver
{
    public function __construct(
        private mixed $redis,
        private string $sessionKey,
        private string $connection = 'default',
        private int $ttl = 604800,
    ) {}

    private function itemsKey(): string
    {
        return "cart:{$this->sessionKey}:items";
    }

    private function conditionsKey(): string
    {
        return "cart:{$this->sessionKey}:conditions";
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey): void
    {
        $items = $this->getItems();
        $conditions = $this->getConditions();

        $this->flush();

        $this->sessionKey = $sessionKey;

        if (! empty($items)) {
            $this->putItems($items);
        }

        if (! empty($conditions)) {
            $this->putConditions($conditions);
        }
    }

    public function getDriverName(): string
    {
        return 'redis';
    }

    public function getItems(): array
    {
        $data = $this->redis->connection($this->connection)->get($this->itemsKey());

        return $data ? json_decode($data, true) : [];
    }

    public function putItems(array $items): void
    {
        $this->redis->connection($this->connection)->setex(
            $this->itemsKey(),
            $this->ttl,
            json_encode($items),
        );
    }

    public function getConditions(): array
    {
        $data = $this->redis->connection($this->connection)->get($this->conditionsKey());

        return $data ? json_decode($data, true) : [];
    }

    public function putConditions(array $conditions): void
    {
        $this->redis->connection($this->connection)->setex(
            $this->conditionsKey(),
            $this->ttl,
            json_encode($conditions),
        );
    }

    public function clearItems(): void
    {
        $this->redis->connection($this->connection)->del($this->itemsKey());
    }

    public function flush(): void
    {
        $this->redis->connection($this->connection)->del($this->itemsKey(), $this->conditionsKey());
    }

    public function getSessionModel(): mixed
    {
        return null;
    }
}
