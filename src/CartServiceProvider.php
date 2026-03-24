<?php

namespace Wearepixel\Cart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('cart.php'),
            ], 'config');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'cart');

        $this->app->singleton('cart', function ($app) {
            $config = config('cart');
            $events = $app['events'];

            $storage = $config['driver'] === 'database'
                ? new $config['storage']['database']['model']
                : $app['session'];

            return new Cart(
                $storage,
                $events,
                'cart',
                session()->getId(),
                $config
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
