<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

beforeEach(function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    $this->cart = new Cart(
        new SessionDriver(new SessionMock, 'SAMPLESESSIONKEY'),
        $events,
        'cart',
        require (__DIR__ . '/../Helpers/ConfigConditionsMock.php')
    );
});

afterEach(function () {
    Mockery::close();
});

describe('quantity-limited item conditions', function () {
    test('condition without applies_to applies to all items', function () {
        $discount = new CartCondition([
            'name' => '5% Off',
            'type' => 'discount',
            'value' => '-5%',
        ]);

        $this->cart->add([
            'id' => 1,
            'name' => 'Widget',
            'price' => 50.00,
            'quantity' => 2,
            'attributes' => [],
            'conditions' => $discount,
        ]);

        // Without applies_to: 50*0.95 * 2 = 95
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(95.00);
        expect($this->cart->getSubTotal())->toEqual(95.00);
    });

    test('condition with applies_to only discounts that many items', function () {
        $discount = new CartCondition([
            'name' => '5% Off First Item',
            'type' => 'discount',
            'value' => '-5%',
            'applies_to' => 1,
        ]);

        $this->cart->add([
            'id' => 1,
            'name' => 'Widget',
            'price' => 50.00,
            'quantity' => 2,
            'attributes' => [],
            'conditions' => $discount,
        ]);

        // applies_to: 1 → (50*0.95) + 50 = 97.5
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(97.50);
        expect($this->cart->getSubTotal())->toEqual(97.50);
    });

    test('applies_to greater than or equal to quantity discounts all items', function () {
        $discount = new CartCondition([
            'name' => '5% Off',
            'type' => 'discount',
            'value' => '-5%',
            'applies_to' => 5,
        ]);

        $this->cart->add([
            'id' => 1,
            'name' => 'Widget',
            'price' => 50.00,
            'quantity' => 2,
            'attributes' => [],
            'conditions' => $discount,
        ]);

        // applies_to 5 >= qty 2, so all items discounted: 50*0.95 * 2 = 95
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(95.00);
    });

    test('applies_to of zero means condition does not apply to any item', function () {
        $discount = new CartCondition([
            'name' => '5% Off',
            'type' => 'discount',
            'value' => '-5%',
            'applies_to' => 0,
        ]);

        $this->cart->add([
            'id' => 1,
            'name' => 'Widget',
            'price' => 50.00,
            'quantity' => 2,
            'attributes' => [],
            'conditions' => $discount,
        ]);

        // applies_to 0: no items discounted → 50 * 2 = 100
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(100.00);
    });

    test('multiple conditions where only one has applies_to', function () {
        $limitedDiscount = new CartCondition([
            'name' => '10% Off First Item',
            'type' => 'discount',
            'value' => '-10%',
            'applies_to' => 1,
        ]);

        $globalDiscount = new CartCondition([
            'name' => '5% Off All',
            'type' => 'discount',
            'value' => '-5%',
        ]);

        $this->cart->add([
            'id' => 1,
            'name' => 'Widget',
            'price' => 100.00,
            'quantity' => 2,
            'attributes' => [],
            'conditions' => [$limitedDiscount, $globalDiscount],
        ]);

        // Item 1: 100 * 0.90 * 0.95 = 85.50
        // Item 2: 100 * 0.95 = 95.00 (only global applies)
        // Total: 180.50
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(180.50);
    });

    test('getAppliesToQuantity returns null when not set', function () {
        $condition = new CartCondition([
            'name' => 'Discount',
            'type' => 'discount',
            'value' => '-5%',
        ]);

        expect($condition->getAppliesToQuantity())->toBeNull();
    });

    test('getAppliesToQuantity returns the set value', function () {
        $condition = new CartCondition([
            'name' => 'Discount',
            'type' => 'discount',
            'value' => '-5%',
            'applies_to' => 3,
        ]);

        expect($condition->getAppliesToQuantity())->toBe(3);
    });
});
