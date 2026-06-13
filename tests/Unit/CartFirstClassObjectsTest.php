<?php

declare(strict_types=1);

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Coupons\Coupon;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Exceptions\InvalidCouponException;
use Wearepixel\Cart\Shipping\ShippingRate;
use Wearepixel\Cart\Tax\TaxRule;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

function makeCart(): Cart
{
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    return new Cart(
        new SessionDriver(new SessionMock, 'TESTKEY'),
        $events,
        'cart',
        require __DIR__ . '/../Helpers/ConfigMock.php',
    );
}

class TestCoupon extends Coupon
{
    protected string $code = 'SAVE10';
    protected string $value = '-10%';
    public function isValid(): bool { return true; }
}

class InvalidTestCoupon extends Coupon
{
    protected string $code = 'EXPIRED';
    protected string $value = '-10%';
    public function isValid(): bool { return false; }
}

class TestTax extends TaxRule
{
    protected string $name = 'GST';
    protected string $value = '10%';
    public function isApplicable(): bool { return true; }
}

class TestShipping extends ShippingRate
{
    protected string $name = 'Flat Rate';
    protected string $value = '+10';
    public function isApplicable(): bool { return true; }
}

beforeEach(function () {
    $this->cart = makeCart();
    $this->cart->add(1, 'Widget', 100.00, 1);
});

afterEach(fn() => Mockery::close());

describe('Cart first-class objects', function () {
    test('Cart::coupon() applies condition from a Coupon instance', function () {
        $this->cart->coupon(new TestCoupon);

        expect($this->cart->getCondition('SAVE10'))->toBeInstanceOf(CartCondition::class);
        expect($this->cart->getSubTotal())->toEqual(90.00);
    });

    test('Cart::coupon() throws when coupon is invalid', function () {
        expect(fn() => $this->cart->coupon(new InvalidTestCoupon))
            ->toThrow(InvalidCouponException::class);
    });

    test('Cart::tax() applies condition from a TaxRule instance', function () {
        $this->cart->tax(new TestTax);

        expect($this->cart->getCondition('GST'))->toBeInstanceOf(CartCondition::class);
        expect($this->cart->getSubTotal())->toEqual(110.00);
    });

    test('Cart::shipping() applies condition from a ShippingRate instance', function () {
        $this->cart->shipping(new TestShipping);

        expect($this->cart->getCondition('Flat Rate'))->toBeInstanceOf(CartCondition::class);
        expect($this->cart->getTotal())->toEqual(110.00);
    });

    test('raw Cart::condition() still works alongside first-class objects', function () {
        $this->cart->coupon(new TestCoupon);
        $this->cart->condition(new CartCondition([
            'name'   => 'Manual Discount',
            'type'   => 'discount',
            'value'  => '-5',
            'target' => 'subtotal',
        ]));

        expect($this->cart->getConditions()->count())->toBe(2);
    });
});
