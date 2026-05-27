<?php

declare(strict_types=1);

namespace Wearepixel\Cart;

use Illuminate\Support\ServiceProvider;
use Wearepixel\Cart\Commands\DebugCommand;
use Wearepixel\Cart\Commands\InstallCommand;
use Wearepixel\Cart\Commands\MakeCouponCommand;
use Wearepixel\Cart\Commands\MakeDriverCommand;
use Wearepixel\Cart\Commands\MakeShippingCommand;
use Wearepixel\Cart\Commands\MakeTaxCommand;

class CartServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('cart.php'),
            ], 'cart-config');

            $this->commands([
                InstallCommand::class,
                MakeCouponCommand::class,
                MakeTaxCommand::class,
                MakeShippingCommand::class,
                MakeDriverCommand::class,
                DebugCommand::class,
            ]);
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
