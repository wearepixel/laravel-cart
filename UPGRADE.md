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

---

### Recommended v3 patterns

v3 is fully backwards compatible - your existing code will continue to work unchanged. The following patterns are recommended for new code and worth migrating to when you touch existing cart logic.

#### 1. Publish the updated config

Run the installer to get the new `driver` key and storage configuration in your `config/cart.php`:

```bash
php artisan cart:install
```

The default driver is `session`, so behaviour is identical to v2 unless you change it.

#### 2. Replace raw CartCondition arrays with first-class objects

Generate typed classes with Artisan instead of constructing arrays inline:

```bash
php artisan cart:make:coupon TenPercentOff
php artisan cart:make:tax GstTaxRule
php artisan cart:make:shipping FlatRateShipping
```

**Before:**

```php
$condition = new CartCondition([
    'name'   => 'SAVE10',
    'type'   => 'coupon',
    'target' => 'subtotal',
    'value'  => '-10%',
]);

Cart::condition($condition);
```

**After:**

```php
// app/Cart/Coupons/TenPercentOff.php
class TenPercentOff extends Coupon
{
    protected string $code = 'SAVE10';
    protected string $value = '-10%';
    protected string $target = 'subtotal';

    public function isValid(): bool
    {
        return true;
    }
}

Cart::coupon(new TenPercentOff);
Cart::tax(new GstTaxRule);
Cart::shipping(new FlatRateShipping);
```

The same pattern applies to `TaxRule` (implement `isApplicable()`) and `ShippingRate` (implement `isApplicable()`).

#### 3. Use Cart::fake() in tests instead of mocking

**Before:**

```php
// Mocking the session or binding a fake driver manually
$this->mock(CartDriver::class, function ($mock) {
    $mock->shouldReceive(...);
});
```

**After:**

```php
$factory = app('cart')->fake();

$factory->withItems(3)->withCondition(new GstTaxRule);

$cart = app('cart');
$cart->assertCount(3);
$cart->assertConditionApplied('GST');
$cart->assertTotal(33.00);
```

`Cart::fake()` swaps the driver to an in-memory `NullDriver` for the duration of the test - no session, database, or Redis needed.

#### 4. Switch to a persistent driver (optional)

If you want cart state to survive beyond a session, update `config/cart.php`:

```php
'driver' => 'database', // or 'redis'
```

For the database driver, publish and run the migration:

```bash
php artisan cart:install
php artisan migrate
```

See the [Storage Drivers](README.md#storage-drivers) section of the README for full configuration options.
