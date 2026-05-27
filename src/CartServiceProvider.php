<?php

declare(strict_types=1);

namespace Wearepixel\Cart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('cart.php'),
            ], 'config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'cart');

        $this->app->singleton('cart.manager', fn($app) => new CartManager($app));

        $this->app->singleton('cart', function ($app) {
            $config = config('cart');
            $events = $app['events'];
            $driver = $app['cart.manager']->driver();

            return new Cart($driver, $events, 'cart', $config);
        });
    }

    public function provides(): array
    {
        return [];
    }
}
