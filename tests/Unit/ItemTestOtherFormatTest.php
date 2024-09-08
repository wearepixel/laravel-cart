<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

beforeEach(function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    $this->cart = new Cart(
        new SessionMock,
        $events,
        'shopping',
        'SAMPLESESSIONKEY',
        require(__DIR__ . '/../Helpers/ConfigMockOtherFormat.php')
    );
});

afterEach(function () {
    Mockery::close();
});

test('item get sum price using property', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, []);

    $item = $this->cart->get(455);

    expect($item->getPriceSum())->toEqual('201.98', 'Item summed price should be 201.98');
});

test('item get sum price using array style', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, []);

    $item = $this->cart->get(455);

    expect($item->getPriceSum())->toEqual('201.98', 'Item summed price should be 201.98');
});
