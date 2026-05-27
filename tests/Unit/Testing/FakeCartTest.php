<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

function makeFakeableCart(): Cart
{
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    return new Cart(
        new SessionDriver(new SessionMock, 'TESTKEY'),
        $events,
        'cart',
        require __DIR__ . '/../../Helpers/ConfigMock.php',
    );
}

beforeEach(function () {
    $this->cart = makeFakeableCart();
});

afterEach(fn() => Mockery::close());

describe('Cart::fake()', function () {
    test('returns the CartFactory instance', function () {
        expect($this->cart->fake())->toBeInstanceOf(\Wearepixel\Cart\Testing\CartFactory::class);
    });

    test('swaps driver to NullDriver', function () {
        $this->cart->fake();
        expect($this->cart->getDriver()->getDriverName())->toBe('null');
    });

    test('Cart operations work after fake()', function () {
        $this->cart->fake();
        $this->cart->add(1, 'Widget', 50.00, 2);

        expect($this->cart->isEmpty())->toBeFalse();
        expect($this->cart->getContent()->count())->toBe(1);
    });

    test('assertContains passes when item is in cart', function () {
        $this->cart->fake();
        $this->cart->add(1, 'Widget', 50.00, 1);

        expect(fn() => $this->cart->assertContains(1))->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertContains fails when item is not in cart', function () {
        $this->cart->fake();

        expect(fn() => $this->cart->assertContains(99))->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertCount passes with correct count', function () {
        $this->cart->fake();
        $this->cart->add(1, 'A', 10.00, 1);
        $this->cart->add(2, 'B', 20.00, 1);

        expect(fn() => $this->cart->assertCount(2))->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertTotalQuantity passes with correct quantity', function () {
        $this->cart->fake();
        $this->cart->add(1, 'A', 10.00, 3);

        expect(fn() => $this->cart->assertTotalQuantity(3))->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertSubTotal passes with correct subtotal', function () {
        $this->cart->fake();
        $this->cart->add(1, 'Widget', 50.00, 2);

        expect(fn() => $this->cart->assertSubTotal(100.00))->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertTotal passes with correct total', function () {
        $this->cart->fake();
        $this->cart->add(1, 'Widget', 50.00, 1);

        expect(fn() => $this->cart->assertTotal(50.00))->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertConditionApplied passes when condition exists', function () {
        $this->cart->fake();
        $this->cart->condition(new CartCondition(['name' => 'GST', 'type' => 'tax', 'value' => '10%', 'target' => 'subtotal']));

        expect(fn() => $this->cart->assertConditionApplied('GST'))->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertConditionApplied fails when condition does not exist', function () {
        $this->cart->fake();

        expect(fn() => $this->cart->assertConditionApplied('GST'))->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertEmpty passes when cart is empty', function () {
        $this->cart->fake();

        expect(fn() => $this->cart->assertEmpty())->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });

    test('assertNotEmpty passes when cart has items', function () {
        $this->cart->fake();
        $this->cart->add(1, 'Widget', 10.00, 1);

        expect(fn() => $this->cart->assertNotEmpty())->not->toThrow(\PHPUnit\Framework\AssertionFailedError::class);
    });
});
