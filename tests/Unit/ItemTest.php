<?php

use Pixeldigital\Cart\Cart;
use Pixeldigital\Cart\CartCondition;
use Pixeldigital\Cart\Tests\Helpers\MockProduct;
use Pixeldigital\Cart\Tests\Helpers\SessionMock;

beforeEach(function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    $this->cart = new Cart(
        new SessionMock,
        $events,
        'shopping',
        'SAMPLESESSIONKEY',
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );
});

afterEach(function () {
    Mockery::close();
});

test('item get sum price using property', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, []);

    $item = $this->cart->get(455);

    expect($item->getPriceSum())->toEqual(201.98, 'Item summed price should be 201.98');
});

test('item get sum price using array style', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, []);

    $item = $this->cart->get(455);

    expect($item->getPriceSum())->toEqual(201.98, 'Item summed price should be 201.98');
});

test('item get conditions empty', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, []);

    $item = $this->cart->get(455);

    expect($item->getConditions())->toBeEmpty('Item should have no conditions');
});

test('item get conditions with conditions', function () {
    $itemCondition1 = new \Pixeldigital\Cart\CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'item',
        'value' => '-5%',
    ]);

    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'target' => 'item',
        'value' => '-25',
    ]);

    $this->cart->add(455, 'Sample Item', 100.99, 2, [], [$itemCondition1, $itemCondition2]);

    $item = $this->cart->get(455);

    expect($item->getConditions())->toHaveCount(2, 'Item should have two conditions');
});

test('item associate model', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, [])->associate(MockProduct::class);

    $item = $this->cart->get(455);

    expect($item->associatedModel)->toEqual(MockProduct::class, 'Item assocaited model should be ' . MockProduct::class);
});

test('it will throw an exception when a non existing model is being associated', function () {
    $this->expectException(\Pixeldigital\Cart\Exceptions\UnknownModelException::class);
    $this->expectExceptionMessage('The supplied model SomeModel does not exist.');

    $this->cart->add(1, 'Test item', 1, 10.00)->associate('SomeModel');
});

test('item get model', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, [])->associate(MockProduct::class);

    $item = $this->cart->get(455);

    expect($item->model)->toBeInstanceOf(MockProduct::class);
    expect($item->model->name)->toEqual('Sample Item');
    expect($item->model->id)->toEqual(455);
});

test('item get model will return null if it has no model', function () {
    $this->cart->add(455, 'Sample Item', 100.99, 2, []);

    $item = $this->cart->get(455);

    expect($item->model)->toEqual(null);
});
