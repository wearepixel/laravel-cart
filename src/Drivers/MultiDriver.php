<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Drivers;

use InvalidArgumentException;
use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class MultiDriver implements CartDriver
{
    /** @param CartDriver[] $drivers */
    public function __construct(private array $drivers)
    {
        if (empty($drivers)) {
            throw new InvalidArgumentException('MultiDriver requires at least one driver.');
        }
    }

    private function primary(): CartDriver
    {
        return $this->drivers[0];
    }

    public function getSessionKey(): string
    {
        return $this->primary()->getSessionKey();
    }

    public function setSessionKey(string $sessionKey): void
    {
        foreach ($this->drivers as $driver) {
            $driver->setSessionKey($sessionKey);
        }
    }

    public function getDriverName(): string
    {
        return 'multi';
    }

    public function getItems(): array
    {
        return $this->primary()->getItems();
    }

    public function putItems(array $items): void
    {
        foreach ($this->drivers as $driver) {
            $driver->putItems($items);
        }
    }

    public function getConditions(): array
    {
        return $this->primary()->getConditions();
    }

    public function putConditions(array $conditions): void
    {
        foreach ($this->drivers as $driver) {
            $driver->putConditions($conditions);
        }
    }

    public function clearItems(): void
    {
        foreach ($this->drivers as $driver) {
            $driver->clearItems();
        }
    }

    public function flush(): void
    {
        foreach ($this->drivers as $driver) {
            $driver->flush();
        }
    }

    public function getSessionModel(): mixed
    {
        return $this->primary()->getSessionModel();
    }
}
