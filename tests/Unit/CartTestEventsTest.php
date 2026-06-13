<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

beforeEach(function () {});

afterEach(function () {
    Mockery::close();
});

test('event cart Created', function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Created', Mockery::type('array'), true);

    $cart = new Cart(
        new SessionDriver(new SessionMock, 'SAMPLESESSIONKEY'),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

    expect(true)->toBeTrue();
});

test('event cart adding', function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Created', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Adding', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Added', Mockery::type('array'), true);

    $cart = new Cart(
        new SessionDriver(new SessionMock, 'SAMPLESESSIONKEY'),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

    $cart->add(455, 'Sample Item', 100.99, 2, []);

    expect(true)->toBeTrue();
});

test('event cart adding multiple times', function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Created', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(2)->with('LaravelCart.Adding', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(2)->with('LaravelCart.Added', Mockery::type('array'), true);

    $cart = new Cart(
        new SessionDriver(new SessionMock, 'SAMPLESESSIONKEY'),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

    $cart->add(455, 'Sample Item 1', 100.99, 2, []);
    $cart->add(562, 'Sample Item 2', 100.99, 2, []);

    expect(true)->toBeTrue();
});

test('event cart adding multiple times scenario two', function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Created', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(3)->with('LaravelCart.Adding', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(3)->with('LaravelCart.Added', Mockery::type('array'), true);

    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 4,
            'attributes' => [],
        ],
        [
            'id' => 856,
            'name' => 'Sample Item 3',
            'price' => 50.25,
            'quantity' => 4,
            'attributes' => [],
        ],
    ];

    $cart = new Cart(
        new SessionDriver(new SessionMock, 'SAMPLESESSIONKEY'),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

    $cart->add($items);

    expect(true)->toBeTrue();
});

test('event cart remove item', function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Created', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(3)->with('LaravelCart.Adding', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(3)->with('LaravelCart.Added', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(1)->with('LaravelCart.Removing', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(1)->with('LaravelCart.Removed', Mockery::type('array'), true);

    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 4,
            'attributes' => [],
        ],
        [
            'id' => 856,
            'name' => 'Sample Item 3',
            'price' => 50.25,
            'quantity' => 4,
            'attributes' => [],
        ],
    ];

    $cart = new Cart(
        new SessionDriver(new SessionMock, 'SAMPLESESSIONKEY'),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

    $cart->add($items);

    $cart->remove(456);

    expect(true)->toBeTrue();
});

test('event cart clear', function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Created', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(3)->with('LaravelCart.Adding', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->times(3)->with('LaravelCart.Added', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Clearing', Mockery::type('array'), true);
    $events->shouldReceive('dispatch')->once()->with('LaravelCart.Cleared', Mockery::type('array'), true);

    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 4,
            'attributes' => [],
        ],
        [
            'id' => 856,
            'name' => 'Sample Item 3',
            'price' => 50.25,
            'quantity' => 4,
            'attributes' => [],
        ],
    ];

    $cart = new Cart(
        new SessionDriver(new SessionMock, 'SAMPLESESSIONKEY'),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

    $cart->add($items);

    $cart->clear();

    expect(true)->toBeTrue();
});
