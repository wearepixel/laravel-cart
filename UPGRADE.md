# Upgrade Guide

## v2.x to v3.0

### Breaking changes

#### Constructor signature

`Cart` no longer accepts a raw session/database storage object as its first constructor argument. It now requires a `CartDriver` instance. If you were resolving `Cart` directly (not via the service container), update your instantiation:

**Before:**

```php
new Cart($sessionStorage, $events, 'cart', $sessionKey, $config);
```

**After:**

```php
use Wearepixel\Cart\Drivers\SessionDriver;

new Cart(new SessionDriver($session, $sessionKey), $events, 'cart', $config);
```

Applications that use the service container (the normal case) are unaffected - `CartServiceProvider` handles this automatically.

#### Publish tag renamed

The config publish tag changed from `config` to `cart-config` to avoid conflicts with other packages:

**Before:**

```bash
php artisan vendor:publish --provider="Wearepixel\Cart\CartServiceProvider" --tag=config
```

**After:**

```bash
php artisan vendor:publish --provider="Wearepixel\Cart\CartServiceProvider" --tag=cart-config
# or simply:
php artisan cart:install
```

#### CartSession removed

`Wearepixel\Cart\CartSession` has been removed. It is replaced by the driver system. If you extended or referenced `CartSession` directly, switch to the appropriate driver (`SessionDriver`, `DatabaseDriver`, etc.).

### Non-breaking additions

The following are additive and require no changes to existing code:

- **Storage drivers:** `SessionDriver`, `DatabaseDriver`, `RedisDriver`, `NullDriver`, `MultiDriver` - configure via `config/cart.php`
- **CartManager:** resolves drivers by name, accessible via `app('cart.manager')`
- **First-class objects:** `Coupon`, `TaxRule`, `ShippingRate` abstract base classes - optional, existing raw `CartCondition` usage continues to work
- **Cart::fake():** returns a `CartFactory` backed by an in-memory `NullDriver` for testing
- **Assertion methods:** `Cart::assertContains()`, `assertCount()`, `assertTotal()`, etc.
- **HasCart trait:** Livewire reactive cart state
- **ShareCartWithInertia:** static class for Inertia middleware integration
- **Artisan generators:** `cart:install`, `cart:make:coupon`, `cart:make:tax`, `cart:make:shipping`, `cart:make:driver`, `cart:debug`
