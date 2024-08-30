<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

beforeEach(function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    $this->cart1 = new Cart(
        new SessionMock,
        $events,
        'shopping',
        'uniquesessionkey123',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

    $this->cart2 = new Cart(
        new SessionMock,
        $events,
        'wishlist',
        'uniquesessionkey456',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );
});

afterEach(function () {
    Mockery::close();
});

test('cart multiple instances', function () {
    // add 3 items on cart 1
    $itemsForCart1 = [
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

    $this->cart1->add($itemsForCart1);

    expect($this->cart1->isEmpty())->toBeFalse('Cart should not be empty');
    expect($this->cart1->getContent()->toArray())->toHaveCount(3, 'Cart should have 3 items');
    expect($this->cart1->getInstanceName())->toEqual('shopping', 'Cart 1 should have instance name of "shopping"');

    // add 1 item on cart 2
    $itemsForCart2 = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => [],
        ],
    ];

    $this->cart2->add($itemsForCart2);

    expect($this->cart2->isEmpty())->toBeFalse('Cart should not be empty');
    expect($this->cart2->getContent()->toArray())->toHaveCount(1, 'Cart should have 3 items');
    expect($this->cart2->getInstanceName())->toEqual('wishlist', 'Cart 2 should have instance name of "wishlist"');
});
