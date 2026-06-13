# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [3.0.0] - TBD

### Added

- **Storage driver system** (`CartDriver` interface, `CartManager`, `SessionDriver`, `DatabaseDriver`, `RedisDriver`, `NullDriver`, `MultiDriver`)
- **`Cart::fake()`** - swaps the active driver for a `NullDriver` and returns a `CartFactory` for fluent test state seeding
- **Assertion methods** on `Cart`: `assertContains()`, `assertCount()`, `assertTotalQuantity()`, `assertSubTotal()`, `assertTotal()`, `assertConditionApplied()`, `assertEmpty()`, `assertNotEmpty()`
- **`CartFactory`** - fluent builder for seeding test cart state (`withItems()`, `withCondition()`, `withCoupon()`)
- **`Coupon` abstract class** - extend to create first-class coupon objects that convert to `CartCondition`
- **`TaxRule` abstract class** - extend to create first-class tax rules that convert to `CartCondition`
- **`ShippingRate` abstract class** - extend to create first-class shipping rates that convert to `CartCondition`
- **`Cart::coupon()`**, **`Cart::tax()`**, **`Cart::shipping()`** - accept first-class objects or raw `CartCondition`
- **`HasCart` trait** - Livewire-aware trait with reactive `$cartItems`, `$cartTotal`, `$cartSubTotal`, `$cartCount` and proxy methods
- **`ShareCartWithInertia`** - static class for sharing cart state via `HandleInertiaRequests::share()`
- **`cart:install`** Artisan command - publishes config and scaffolds `app/Cart/` directory structure
- **`cart:make:coupon`** Artisan command - generates a `Coupon` subclass stub
- **`cart:make:tax`** Artisan command - generates a `TaxRule` subclass stub
- **`cart:make:shipping`** Artisan command - generates a `ShippingRate` subclass stub
- **`cart:make:driver`** Artisan command - generates a `CartDriver` implementation stub
- **`cart:debug`** Artisan command - dumps current cart state (non-production only)
- Laravel 13 support

### Changed

- `Cart` constructor now accepts a `CartDriver` instance instead of raw session/database storage
- Config publish tag renamed from `config` to `cart-config`

### Removed

- `Wearepixel\Cart\CartSession` - replaced by the driver system

## [2.1.1] - 2026-05-27

### Fixed

- PHP 8.4 deprecation warnings from Mockery implicit-nullable parameters in test bootstrap
- Upgraded CI runners from deprecated `ubuntu-20.04` to `ubuntu-latest`

### Changed

- `pestphp/pest` upgraded from `^3.0` to `^4.7`

## [2.1.0] - 2024-01-01

### Added

- Laravel 12 support

## [2.0.0] - 2023-01-01

### Added

- Initial v2 release
