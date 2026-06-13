<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Drivers\DatabaseDriver;
use Wearepixel\Cart\Tests\Helpers\MockCartModel;

beforeEach(function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    $this->cart = new Cart(
        new DatabaseDriver(
            MockCartModel::class,
            'session_id',
            'items',
            'conditions',
            'SAMPLESESSIONKEY',
        ),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigDatabaseMock.php')
    );
});

afterEach(function () {
    Mockery::close();
});

describe('database cart', function () {
    test('can add an item', function () {
        $this->cart->add(1, 'Sample Item', 100.99, 2, []);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart content should be 1');
        expect($this->cart->getContent()->first()['id'])->toEqual(1, 'Item added has ID of 1 so first content ID should be 1');
        expect($this->cart->getContent()->first()['price'])->toEqual(100.99, 'Item added has price of 100.99 so first content price should be 100.99');

        expect(MockCartModel::all()->count())->toEqual(1, 'The carts database table should have 1 row');
    });

    test('can add and remove an item', function () {
        $this->cart->add(1, 'Sample Item', 100.99, 2, []);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart content should be 1');
        expect($this->cart->getContent()->first()['id'])->toEqual(1, 'Item added has ID of 1 so first content ID should be 1');
        expect($this->cart->getContent()->first()['price'])->toEqual(100.99, 'Item added has price of 100.99 so first content price should be 100.99');

        expect(MockCartModel::all()->count())->toEqual(1, 'The carts database table should have 1 row');

        $this->cart->remove(1);

        $cartModel = MockCartModel::first();

        expect($this->cart->isEmpty())->toBeTrue('Cart should be empty');
        expect($this->cart->getContent()->count())->toEqual(0, 'Cart content should be 0');
        expect($cartModel->items)->toEqual([], 'The carts database table should have 0 row');
    });

    test('can add an item with an item level condition', function () {
        $saleCondition = new CartCondition([
            'name' => '5% Discount',
            'type' => 'sale',
            'value' => '-5%',
        ]);

        $item = [
            'id' => 1,
            'name' => 'Chicken Curry',
            'price' => 18.95,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => $saleCondition,
        ];

        $this->cart->add($item);

        expect($this->cart->get(1)->getPriceSum())->toEqual(18.95);
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(18);
        expect($this->cart->getSubTotal())->toEqual(18, 'Cart should have subtotal of 18');
        expect($this->cart->getTotal())->toEqual(18, 'Cart should have total of 18');
    });

    test('can add an item with multiple item level conditions', function () {
        $saleCondition = new CartCondition([
            'name' => '5% Discount',
            'type' => 'sale',
            'value' => '-5%',
        ]);

        $gstCondition = new CartCondition([
            'name' => 'GST',
            'type' => 'gst',
            'value' => '2.5%',
        ]);

        $item = [
            'id' => 1,
            'name' => 'Chicken Curry',
            'price' => 18.95,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [$saleCondition, $gstCondition],
        ];

        $this->cart->add($item);

        expect($this->cart->get(1)->getPriceSum())->toEqual(18.95);
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(18.45);
        expect($this->cart->getSubTotal())->toEqual(18.45, 'Cart should have subtotal of 18.45');
        expect($this->cart->getTotal())->toEqual(18.45, 'Cart should have total of 18.45');
    });

    test('cart-level conditions survive database round-trip', function () {
        $this->cart->condition(new CartCondition([
            'name'   => 'GST',
            'type'   => 'tax',
            'value'  => '10%',
            'target' => 'subtotal',
        ]));

        // Create a fresh cart instance pointing at the same DB row
        $events2 = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
        $events2->shouldReceive('dispatch');

        $cart2 = new \Wearepixel\Cart\Cart(
            new \Wearepixel\Cart\Drivers\DatabaseDriver(
                \Wearepixel\Cart\Tests\Helpers\MockCartModel::class,
                'session_id',
                'items',
                'conditions',
                'SAMPLESESSIONKEY',
            ),
            $events2,
            'cart',
            require (__DIR__ . '/../Helpers/ConfigDatabaseMock.php')
        );

        $condition = $cart2->getCondition('GST');

        expect($condition)->not->toBeNull();
        expect($condition->getName())->toBe('GST');
        expect($condition->getValue())->toBe('10%');
    });

    test('can clear a simple cart', function () {
        $this->cart->add(1, 'Sample Item', 100.99, 2, []);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart content should be 1');
        expect($this->cart->getContent()->first()['id'])->toEqual(1, 'Item added has ID of 1 so first content ID should be 1');
        expect($this->cart->getContent()->first()['price'])->toEqual(100.99, 'Item added has price of 100.99 so first content price should be 100.99');
        expect(MockCartModel::all()->count())->toEqual(1, 'The carts database table should have 1 row');

        $this->cart->clear();

        expect($this->cart->isEmpty())->toBeTrue('Cart should be empty');
        expect($this->cart->getContent()->count())->toEqual(0, 'Cart content should be 0');
        expect(MockCartModel::all()->count())->toEqual(0, 'The carts database table should have 0 rows');
    });

    test('can clear cart conditions after clearing the cart', function () {
        $condition = new CartCondition([
            'name' => '10% Off',
            'type' => 'coupon',
            'target' => 'subtotal',
            'value' => '-10%',
        ]);

        $this->cart->add(1, 'Sample Item', 100.99, 2, []);
        $this->cart->condition($condition);

        $this->cart->clear();

        // This was throwing "Call to a member function save() on array"
        // because clear() replaced $this->session with a plain array
        expect(fn () => $this->cart->clearCartConditions())->not->toThrow(Error::class);
        expect($this->cart->getConditions())->toBeEmpty();
    });
});
