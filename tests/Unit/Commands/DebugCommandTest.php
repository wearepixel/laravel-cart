<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\Commands\DebugCommand;
use Wearepixel\Cart\Drivers\NullDriver;

function makeDebugCart(): Cart
{
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    return new Cart(
        new NullDriver('debug-session'),
        $events,
        'cart',
        require __DIR__ . '/../../Helpers/ConfigMock.php',
    );
}

afterEach(fn() => Mockery::close());

describe('DebugCommand::debugData()', function () {
    test('returns driver name', function () {
        $cart = makeDebugCart();
        $data = DebugCommand::debugData($cart);
        expect($data['driver'])->toBe('null');
    });

    test('returns item count', function () {
        $cart = makeDebugCart();
        $cart->add(1, 'Widget', 10.00, 1);
        $data = DebugCommand::debugData($cart);
        expect($data['item_count'])->toBe(1);
    });

    test('returns total quantity', function () {
        $cart = makeDebugCart();
        $cart->add(1, 'Widget', 10.00, 3);
        $data = DebugCommand::debugData($cart);
        expect($data['total_quantity'])->toBe(3);
    });

    test('returns subtotal', function () {
        $cart = makeDebugCart();
        $cart->add(1, 'Widget', 25.00, 2);
        $data = DebugCommand::debugData($cart);
        expect($data['subtotal'])->toBe(50.00);
    });

    test('returns total', function () {
        $cart = makeDebugCart();
        $cart->add(1, 'Widget', 25.00, 2);
        $data = DebugCommand::debugData($cart);
        expect($data['total'])->toBe(50.00);
    });

    test('returns empty items array when cart is empty', function () {
        $cart = makeDebugCart();
        $data = DebugCommand::debugData($cart);
        expect($data['items'])->toBeArray()->toBeEmpty();
    });

    test('returns condition names', function () {
        $cart = makeDebugCart();
        $cart->condition(new \Wearepixel\Cart\CartCondition([
            'name' => 'GST', 'type' => 'tax', 'value' => '10%', 'target' => 'subtotal',
        ]));
        $data = DebugCommand::debugData($cart);
        expect($data['conditions'])->toContain('GST');
    });
});
