<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\Concerns\ShareCartWithInertia;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

function makeCartForInertia(): Cart
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
    $this->cart = makeCartForInertia();
});

afterEach(fn() => Mockery::close());

describe('ShareCartWithInertia', function () {
    test('data() returns items, total, subtotal, and count', function () {
        $this->cart->add(1, 'Widget', 50.00, 2);

        $data = ShareCartWithInertia::data($this->cart);

        expect($data)->toHaveKeys(['items', 'total', 'subtotal', 'count']);
        expect($data['count'])->toBe(2);
        expect($data['subtotal'])->toBe(100.00);
        expect($data['total'])->toBe(100.00);
    });

    test('data() returns empty state when cart is empty', function () {
        $data = ShareCartWithInertia::data($this->cart);

        expect($data['items'])->toBeEmpty();
        expect($data['count'])->toBe(0);
    });

    test('data() items are plain arrays suitable for JSON serialization', function () {
        $this->cart->add(1, 'Widget', 50.00, 1);

        $data = ShareCartWithInertia::data($this->cart);

        expect($data['items'])->toBeArray();
        expect(json_encode($data))->toBeString();
    });
});
