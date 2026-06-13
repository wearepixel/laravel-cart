<?php

declare(strict_types=1);

namespace Wearepixel\Cart;

use Illuminate\Support\Manager;
use Wearepixel\Cart\Drivers\MultiDriver;
use Wearepixel\Cart\Drivers\NullDriver;
use Wearepixel\Cart\Drivers\RedisDriver;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Drivers\DatabaseDriver;
use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class CartManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('cart.driver', 'session');
    }

    private function sessionKey(): string
    {
        return $this->container['session']->getId();
    }

    public function createSessionDriver(): CartDriver
    {
        return new SessionDriver(
            $this->container['session'],
            $this->sessionKey(),
        );
    }

    public function createDatabaseDriver(): CartDriver
    {
        $config = $this->config->get('cart.storage.database');

        return new DatabaseDriver(
            $config['model'],
            $config['id'],
            $config['items'],
            $config['conditions'],
            $this->sessionKey(),
        );
    }

    public function createRedisDriver(): CartDriver
    {
        $config = $this->config->get('cart.drivers.redis', []);

        return new RedisDriver(
            $this->container['redis'],
            $this->sessionKey(),
            $config['connection'] ?? 'default',
            $config['ttl'] ?? 604800,
        );
    }

    public function createNullDriver(): CartDriver
    {
        return new NullDriver($this->sessionKey());
    }

    public function createMultiDriver(): CartDriver
    {
        $names = $this->config->get('cart.drivers.multi', ['session', 'database']);
        $drivers = array_map(fn(string $name) => $this->driver($name), $names);

        return new MultiDriver($drivers);
    }
}
