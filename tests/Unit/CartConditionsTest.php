<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

beforeEach(function () {
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    $this->cart = new Cart(
        new SessionMock,
        $events,
        'cart',
        'SAMPLESESSIONKEY',
        require (__DIR__ . '/../Helpers/ConfigConditionsMock.php')
    );
});

afterEach(function () {
    Mockery::close();
});

describe('cart level conditions', function () {
    test('can discount the subtotal by dollar amount', function () {
        $this->cart->add([
            'id' => 456,
            'name' => 'Leather Shoes',
            'price' => 129.99,
            'quantity' => 1,
        ]);

        // add condition to subtotal
        $condition = new CartCondition([
            'name' => '$5 Discount',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '-5',
        ]);

        $this->cart->condition($condition);

        expect($this->cart->getSubTotal())->toEqual(124.99);
        expect($this->cart->getTotal())->toEqual(124.99);
    });

    test('can discount the subtotal by percentage', function () {
        $this->cart->add([
            'id' => 456,
            'name' => 'Leather Shoes',
            'price' => 129.99,
            'quantity' => 1,
        ]);

        // add condition to subtotal
        $condition = new CartCondition([
            'name' => '5% Off',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '-5%',
        ]);

        $this->cart->condition($condition);

        expect($this->cart->getSubTotal())->toEqual(123.49);
        expect($this->cart->getTotal())->toEqual(123.49);
    });

    test('can discount subtotal with multiple negative dollar conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');

        // add condition
        $taxFree = new CartCondition([
            'name' => 'Tax Free',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '-10%',
        ]);

        $freeShipping = new CartCondition([
            'name' => 'Free Shipping',
            'type' => 'shipping',
            'target' => 'subtotal',
            'value' => '-10',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($freeShipping);

        expect($this->cart->getSubTotal())->toEqual(113.52, 'Cart should have sub total of 113.52');
        expect($this->cart->getTotal())->toEqual(113.52, 'Cart should have a total of 113.52');
    });

    test('can discount subtotal with multiple negative percent conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');

        // add condition
        $taxFree = new CartCondition([
            'name' => 'Tax Free',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '-10%',
        ]);

        $freeShipping = new CartCondition([
            'name' => 'Site Wide Discount',
            'type' => 'shipping',
            'target' => 'subtotal',
            'value' => '-1.99%',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($freeShipping);

        expect($this->cart->getSubTotal())->toEqual(121.06, 'Cart should have sub total of 121.06');
        expect($this->cart->getTotal())->toEqual(121.06, 'Cart should have a total of 121.06');
    });

    test('can discount subtotal with multiple mixed conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');

        // add condition
        $taxFree = new CartCondition([
            'name' => 'Tax Free',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '-10%',
        ]);

        $freeShipping = new CartCondition([
            'name' => 'Free Shipping',
            'type' => 'shipping',
            'target' => 'subtotal',
            'value' => '-10',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($freeShipping);

        expect($this->cart->getSubTotal())->toEqual(113.52, 'Cart should have sub total of 113.52');
        expect($this->cart->getTotal())->toEqual(113.52, 'Cart should have a total of 113.52');
    });

    test('can add to the subtotal by dollar amount', function () {
        $this->cart->add([
            'id' => 456,
            'name' => 'Leather Shoes with Bundled Item',
            'price' => 129.99,
            'quantity' => 1,
        ]);

        // add condition to subtotal
        $condition = new CartCondition([
            'name' => 'Bundled Item',
            'type' => 'bundled_item',
            'target' => 'subtotal',
            'value' => '7.99',
        ]);

        $this->cart->condition($condition);

        expect($this->cart->getSubTotal())->toEqual(137.98, 'Cart should have sub total of 137.98');
        expect($this->cart->getTotal())->toEqual(137.98, 'Cart should have total of 137.98');
    });

    test('can add to the subtotal by percentage', function () {
        $this->cart->add([
            'id' => 456,
            'name' => 'Leather Shoes',
            'price' => 129.99,
            'quantity' => 1,
        ]);

        $condition = new CartCondition([
            'name' => 'Tax 10%',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '10%',
        ]);

        $this->cart->condition($condition);

        expect($this->cart->getSubTotal())->toEqual(142.99, 'Cart should have sub total of 142.99');
        expect($this->cart->getTotal())->toEqual(142.99, 'Cart should have total of 142.99');
    });

    test('can add to subtotal with multiple positive dollar conditions', function () {
        $this->cart->add([
            'id' => 456,
            'name' => 'Leather Jacket',
            'price' => 89.99,
            'quantity' => 1,
        ]);

        $shippingProtection = new CartCondition([
            'name' => 'Shipping Protection',
            'type' => 'fee',
            'target' => 'subtotal',
            'value' => '5',
        ]);

        $handling = new CartCondition([
            'name' => 'Handling',
            'type' => 'fee',
            'target' => 'subtotal',
            'value' => '10',
        ]);

        $this->cart->condition($shippingProtection);
        $this->cart->condition($handling);

        expect($this->cart->getSubTotal())->toEqual(104.99, 'Cart should have sub total of 104.99');
        expect($this->cart->getTotal())->toEqual(104.99, 'Cart should have a total of 104.99');
    });

    test('can add to subtotal with multiple positive percentage conditions', function () {
        $this->cart->add([
            'id' => 456,
            'name' => 'Leather Jacket',
            'price' => 89.99,
            'quantity' => 1,
        ]);

        $handlingFee = new CartCondition([
            'name' => 'Handling Fee',
            'type' => 'fee',
            'target' => 'subtotal',
            'value' => '5%',
        ]);

        $packageProtection = new CartCondition([
            'name' => 'Package Protection',
            'type' => 'fee',
            'target' => 'subtotal',
            'value' => '10%',
        ]);

        $this->cart->condition($handlingFee);
        $this->cart->condition($packageProtection);

        expect($this->cart->getSubTotal())->toEqual(103.94, 'Cart should have sub total of 103.94');
        expect($this->cart->getTotal())->toEqual(103.94, 'Cart should have a total of 103.94');
    });

    test('can add to subtotal with mixed dollar and percentage conditions', function () {
        $this->cart->add([
            'id' => 456,
            'name' => 'Sample Item',
            'price' => 100.00,
            'quantity' => 1,
        ]);

        $condition1 = new CartCondition([
            'name' => 'Shipping Fee',
            'type' => 'fee',
            'target' => 'subtotal',
            'value' => '5',
        ]);

        $condition2 = new CartCondition([
            'name' => 'Package Protection',
            'type' => 'fee',
            'target' => 'subtotal',
            'value' => '10%',
        ]);

        $this->cart->condition($condition1);
        $this->cart->condition($condition2);

        expect($this->cart->getSubTotal())->toEqual(115.50, 'Cart should have sub total of 115.50');
        expect($this->cart->getTotal())->toEqual(115.50, 'Cart should have a total of 115.50');
    });

    test('can discount the total by dollar amount', function () {
        $items = [
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(187.49, 'Cart should have total of 187.49');

        $condition = new CartCondition([
            'name' => '$15 Off',
            'type' => 'discount',
            'target' => 'total',
            'value' => '-15',
        ]);

        $this->cart->condition($condition);

        // no changes in subtotal as the condition's target added was for total
        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(172.49, 'Cart should have total of 172.49');
    });

    test('can discount the total by percentage', function () {
        $items = [
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(187.49, 'Cart should have total of 187.49');

        $condition = new CartCondition([
            'name' => 'Discount 10%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-10%',
        ]);

        $this->cart->condition($condition);

        // no changes in subtotal as the condition's target added was for total
        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(168.74, 'Cart should have total of 168.74');
    });

    test('can discount total with multiple negative dollar conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $taxFree = new CartCondition([
            'name' => 'Tax Free',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-10%',
        ]);

        $freeShipping = new CartCondition([
            'name' => 'Free Shipping',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '-10',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($freeShipping);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(113.52, 'Cart should have a total of 113.52');
    });

    test('can discount total with multiple negative percent conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $taxFree = new CartCondition([
            'name' => 'Tax Free',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-10%',
        ]);

        $siteWideDiscount = new CartCondition([
            'name' => 'Site Wide Discount',
            'type' => 'discount',
            'target' => 'total',
            'value' => '-1.99%',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($siteWideDiscount);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(121.06, 'Cart should have a total of 121.06');
    });

    test('can discount total with multiple mixed conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $taxFree = new CartCondition([
            'name' => 'Tax Free',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-10%',
        ]);

        $freeShipping = new CartCondition([
            'name' => 'Free Shipping',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '-10',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($freeShipping);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(113.52, 'Cart should have a total of 113.52');
    });

    test('can add to the total by dollar amount', function () {
        $items = [
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(187.49, 'Cart should have total of 187.49');

        // add condition
        $condition = new CartCondition([
            'name' => 'Shipping',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.99',
        ]);

        $this->cart->condition($condition);

        // no changes in subtotal as the condition's target added was for total
        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(200.48, 'Cart should have total of 200.48');
    });

    test('can add to the total by percentage', function () {
        $items = [
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(187.49, 'Cart should have total of 187.49');

        // add condition
        $condition = new CartCondition([
            'name' => 'Tax 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ]);

        $this->cart->condition($condition);

        // no changes in subtotal as the condition's target added was for total
        expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');
        expect($this->cart->getTotal())->toEqual(210.93, 'Cart should have total of 210.93');
    });

    test('can add to the total with multiple positive dollar conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $taxFree = new CartCondition([
            'name' => 'Tax',
            'type' => 'tax',
            'target' => 'total',
            'value' => '10',
        ]);

        $freeShipping = new CartCondition([
            'name' => 'Shipping',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '15',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($freeShipping);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(162.24, 'Cart should have a total of 162.24');
    });

    test('can add to the total with multiple positive percent conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $taxFree = new CartCondition([
            'name' => 'Tax',
            'type' => 'tax',
            'target' => 'total',
            'value' => '10%',
        ]);

        $siteWideDiscount = new CartCondition([
            'name' => 'Site Wide Surcharge',
            'type' => 'discount',
            'target' => 'total',
            'value' => '1.99%',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($siteWideDiscount);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(153.97, 'Cart should have a total of 153.97');
    });

    test('can add to the total with multiple mixed conditions', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $taxFree = new CartCondition([
            'name' => 'Tax',
            'type' => 'tax',
            'target' => 'total',
            'value' => '10%',
        ]);

        $freeShipping = new CartCondition([
            'name' => 'Shipping',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '15',
        ]);

        $this->cart->condition($taxFree);
        $this->cart->condition($freeShipping);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(165.96, 'Cart should have a total of 165.96');
    });

    test('can update calculated condition values', function () {
        $this->cart->add([
            'id' => 1,
            'name' => 'Apple iPhone 15',
            'price' => 200,
            'quantity' => 1,
            'attributes' => [],
        ]);

        $couponDiscount = new CartCondition([
            'name' => 'Coupon Discount',
            'type' => 'discount',
            'target' => 'subtotal',
            'value' => '-200',
            'order' => 1,
        ]);

        $giftCard = new CartCondition([
            'name' => 'Gift Card',
            'type' => 'discount',
            'target' => 'subtotal',
            'value' => '-200',
            'order' => 2,
        ]);

        $this->cart->condition($couponDiscount);
        $this->cart->condition($giftCard);

        expect($this->cart->getCalculatedValueForCondition('Coupon Discount'))->toEqual(200.0, 'Coupon Discount should be 200.0');
        expect($this->cart->getCalculatedValueForCondition('Gift Card'))->toEqual(0, 'Gift Card should be 0');
        expect($couponDiscount->getCalculatedValue())->toEqual(200.0, 'Coupon Discount value should be 200.0');
        expect($giftCard->getCalculatedValue())->toEqual(0, 'Gift Card value should be 0');
    });

    test('can be cleared', function () {
        $siteWideDiscount = new CartCondition([
            'name' => 'Site Wide Discount',
            'type' => 'discount',
            'target' => 'total',
            'value' => '-5%',
        ]);

        $giftCard = new CartCondition([
            'name' => 'Gift Card',
            'type' => 'gift_card',
            'target' => 'total',
            'value' => '-25',
        ]);

        $item = [
            'id' => 1,
            'name' => 'Backpack',
            'price' => 168.72,
            'quantity' => 1,
            'attributes' => [],
        ];

        $this->cart->add($item);

        $this->cart->condition([$siteWideDiscount, $giftCard]);

        expect($this->cart->getConditions()->count())->toEqual(2, 'Cart should have two conditions');

        $this->cart->clearCartConditions();

        expect($this->cart->getConditions()->count())->toEqual(0, 'Cart should have no conditions now');
    });

    test('calculate the subtotal correctly when one condition makes it $0, and the other adds $10', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $couponCode = new CartCondition([
            'name' => 'Coupon code',
            'type' => 'discount',
            'target' => 'subtotal',
            'order' => '1',
            'value' => '-100%',
        ]);

        $shipping = new CartCondition([
            'name' => 'Shipping',
            'type' => 'shipping',
            'target' => 'subtotal',
            'order' => '2',
            'value' => '10',
        ]);

        $this->cart->condition($couponCode);
        $this->cart->condition($shipping);

        expect($this->cart->getSubTotal())->toEqual(10.00, 'Cart should have sub total of 10.00');
        expect($this->cart->getTotal())->toEqual(10.00, 'Cart should have a total of 10.00');
    });

    test('calculate the subtotal correctly when one condition makes it $0 (with dollars), and the other adds $10', function () {
        $this->cart->add([
            [
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => [],
            ],
        ]);

        expect($this->cart->getSubTotal())->toEqual(137.24, 'Cart should have sub total of 137.24');
        expect($this->cart->getTotal())->toEqual(137.24, 'Cart should have total of 137.24');

        // add conditions
        $couponCode = new CartCondition([
            'name' => 'Coupon code',
            'type' => 'discount',
            'target' => 'subtotal',
            'order' => '1',
            'value' => '-137.24',
        ]);

        $shipping = new CartCondition([
            'name' => 'Shipping',
            'type' => 'shipping',
            'target' => 'subtotal',
            'order' => '2',
            'value' => '10',
        ]);

        $this->cart->condition($couponCode);
        $this->cart->condition($shipping);

        expect($this->cart->getSubTotal())->toEqual(10.00, 'Cart should have sub total of 10.00');
        expect($this->cart->getTotal())->toEqual(10.00, 'Cart should have a total of 10.00');
    });
});

describe('item level conditions', function () {
    test('can add an item with a condition', function () {
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

    test('can add item with multiple negative item conditions', function () {
        $saleCondition = new CartCondition([
            'name' => '5% Sale',
            'type' => 'sale',
            'value' => '-5%',
        ]);

        $tenOff = new CartCondition([
            'name' => '$10 Off',
            'type' => 'promo',
            'value' => '-10',
        ]);

        $randomCondition = new CartCondition([
            'name' => 'Winner',
            'type' => 'winner',
            'value' => '-10',
        ]);

        $item = [
            'id' => 1,
            'name' => 'Apple Headphones',
            'price' => 369.80,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [$saleCondition, $tenOff, $randomCondition],
        ];

        $this->cart->add($item);

        expect($this->cart->get(1)->getPriceSum())->toEqual(369.80, 'Item subtotal with 1 item should be 369.80');
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(331.31, 'Item subtotal with 1 item should be 331.31');
        expect($this->cart->getSubTotal())->toEqual(331.31, 'Cart subtotal with 1 item should be 331.31');
        expect($this->cart->getTotal())->toEqual(331.31, 'Cart total with 1 item should be 331.31');
    });

    test('can add item with multiple positive item conditions', function () {
        $saleCondition = new CartCondition([
            'name' => '5% Surcharge',
            'type' => 'surcharge',
            'value' => '5%',
        ]);

        $tenOff = new CartCondition([
            'name' => 'Handling',
            'type' => 'handling',
            'value' => '8.99',
        ]);

        $randomCondition = new CartCondition([
            'name' => 'Item Protection',
            'type' => 'winner',
            'value' => '14.49',
        ]);

        $item = [
            'id' => 1,
            'name' => 'Apple Headphones',
            'price' => 369.80,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [$saleCondition, $tenOff, $randomCondition],
        ];

        $this->cart->add($item);

        expect($this->cart->get(1)->getPriceSum())->toEqual(369.80, 'Item subtotal with 1 item should be 369.80');
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(411.77, 'Item subtotal with 1 item should be 411.77');
        expect($this->cart->getSubTotal())->toEqual(411.77, 'Cart subtotal with 1 item should be 411.77');
        expect($this->cart->getTotal())->toEqual(411.77, 'Cart total with 1 item should be 411.77');
    });

    test('can add item condition to an item that already has conditions', function () {
        $items = [
            [
                'id' => 1,
                'name' => 'Children\'s Bookcase',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        $discount = new CartCondition([
            'name' => '$25 Off',
            'type' => 'promo',
            'value' => '-25',
        ]);

        $childrenDiscount = new CartCondition([
            'name' => '5% Off Childrens Items',
            'type' => 'coupon',
            'value' => '-5%',
        ]);

        $item = [
            'id' => 2,
            'name' => 'Children\'s Pack of Books',
            'price' => 31.22,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [$discount],
        ];

        $this->cart->add($item);

        // let's prove first we have 1 condition on this item
        expect($this->cart->get(2)['conditions'])->toHaveCount(1, 'Item should have 1 condition');

        // now let's insert a condition on an existing item on the cart
        $this->cart->addItemCondition(2, $childrenDiscount);

        expect($this->cart->get(2)['conditions'])->toHaveCount(2, 'Item should have 2 conditions');
    });

    test('will not take the price below 0', function () {
        $item = [
            'id' => 1,
            'name' => 'Picture Frame',
            'price' => 22.50,
            'quantity' => 1,
            'attributes' => [],
        ];

        $condition = new CartCondition([
            'name' => '$25 Discount',
            'type' => 'promo',
            'value' => '-25',
        ]);

        $this->cart->add($item);
        $this->cart->addItemCondition(1, $condition);

        // Since the product price is 20 and the condition reduces it by 25,
        // check that the item's price has been prevented from dropping below zero.
        expect($this->cart->get(1)->getPriceSumWithConditions())->toEqual(0.00, "The item's price should be prevented from going below zero.");
    });

    // test('get cart condition by condition name', function () {
    //     $itemCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //     ]);
    //     $itemCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 25.00',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$itemCondition1, $itemCondition2]);

    //     // get a condition applied on cart by condition name
    //     $condition = $this->cart->getCondition($itemCondition1->getName());

    //     expect('SALE 5%')->toEqual($condition->getName());
    //     expect('total')->toEqual($condition->getTarget());
    //     expect('sale')->toEqual($condition->getType());
    //     expect('-5%')->toEqual($condition->getValue());
    // });

    // test('remove cart condition by condition name', function () {
    //     $itemCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //     ]);
    //     $itemCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 25.00',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$itemCondition1, $itemCondition2]);

    //     // let's prove first we have now two conditions in the cart
    //     expect($this->cart->getConditions()->count())->toEqual(2, 'Cart should have two conditions');

    //     // now let's remove a specific condition by condition name
    //     $this->cart->removeCartCondition('SALE 5%');

    //     // cart should have now only 1 condition
    //     expect($this->cart->getConditions()->count())->toEqual(1, 'Cart should have one condition');
    //     expect($this->cart->getConditions()->first()->getName())->toEqual('Item Gift Pack 25.00');
    // });

    // test('remove item condition by condition name', function () {
    //     $itemCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'value' => '-5%',
    //     ]);
    //     $itemCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 25.00',
    //         'type' => 'promo',
    //         'value' => '-25',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //         'conditions' => [$itemCondition1, $itemCondition2],
    //     ];

    //     $this->cart->add($item);

    //     // let's very first the item has 2 conditions in it
    //     expect($this->cart->get(456)['conditions'])->toHaveCount(2, 'Item should have two conditions');

    //     // now let's remove a condition on that item using the condition name
    //     $this->cart->removeItemCondition(456, 'SALE 5%');

    //     // now we should have only 1 condition left on that item
    //     expect($this->cart->get(456)['conditions'])->toHaveCount(1, 'Item should have one condition left');
    // });

    // test('remove item condition by condition name scenario two', function () {
    //     // NOTE: in this scenario, we will add the conditions not in array format
    //     $itemCondition = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'value' => '-5%',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //         'conditions' => $itemCondition, // <--not in array format
    //     ];

    //     $this->cart->add($item);

    //     // let's very first the item has 2 conditions in it
    //     expect($this->cart->get(456)['conditions'])->not->toBeEmpty('Item should have one condition in it.');

    //     // now let's remove a condition on that item using the condition name
    //     $this->cart->removeItemCondition(456, 'SALE 5%');

    //     // now we should have only 1 condition left on that item
    //     expect($this->cart->get(456)['conditions'])->toBeEmpty('Item should have no condition now');
    // });

    // test('clear item conditions', function () {
    //     $itemCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'value' => '-5%',
    //     ]);
    //     $itemCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 25.00',
    //         'type' => 'promo',
    //         'value' => '-25',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //         'conditions' => [$itemCondition1, $itemCondition2],
    //     ];

    //     $this->cart->add($item);

    //     // let's very first the item has 2 conditions in it
    //     expect($this->cart->get(456)['conditions'])->toHaveCount(2, 'Item should have two conditions');

    //     // now let's remove all condition on that item
    //     $this->cart->clearItemConditions(456);

    //     // now we should have only 0 condition left on that item
    //     expect($this->cart->get(456)['conditions'])->toHaveCount(0, 'Item should have no conditions now');
    // });

    // test('clear cart conditions', function () {
    //     // NOTE:
    //     // This only clears all conditions that has been added in a cart bases
    //     // this does not remove conditions on per item bases
    //     $itemCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //     ]);
    //     $itemCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 25.00',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$itemCondition1, $itemCondition2]);

    //     // let's prove first we have now two conditions in the cart
    //     expect($this->cart->getConditions()->count())->toEqual(2, 'Cart should have two conditions');

    //     // now let's clear cart conditions
    //     $this->cart->clearCartConditions();

    //     // cart should have now only 1 condition
    //     expect($this->cart->getConditions()->count())->toEqual(0, 'Cart should have no conditions now');
    // });

    // test('get calculated value of a condition', function () {
    //     $this->cart->clear();

    //     $cartCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //     ]);
    //     $cartCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 25.00',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$cartCondition1, $cartCondition2]);

    //     $subTotal = $this->cart->getSubTotal();

    //     expect($subTotal)->toEqual(100, 'Subtotal should be 100');

    //     // way 1
    //     // now we will get the calculated value of the condition 1
    //     $cond1 = $this->cart->getCondition('SALE 5%');
    //     expect($cond1->getCalculatedValue($subTotal))->toEqual(5, 'The calculated value must be 5');

    //     // way 2
    //     // get all cart conditions and get their calculated values
    //     $conditions = $this->cart->getConditions();
    //     expect($conditions['SALE 5%']->getCalculatedValue($subTotal))->toEqual(5, 'First condition calculated value must be 5');
    //     expect($conditions['Item Gift Pack 25.00']->getCalculatedValue($subTotal))->toEqual(25, 'First condition calculated value must be 5');
    // });

    // test('get conditions by type', function () {
    //     $cartCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //     ]);
    //     $cartCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 25.00',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //     ]);
    //     $cartCondition3 = new CartCondition([
    //         'name' => 'Item Less 8%',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-8%',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$cartCondition1, $cartCondition2, $cartCondition3]);

    //     // now lets get all conditions added in the cart with the type "promo"
    //     $promoConditions = $this->cart->getConditionsByType('promo');

    //     expect($promoConditions->count())->toEqual(2, 'We should have 2 items as promo condition type.');
    // });

    // test('remove conditions by type', function () {
    //     // NOTE:
    //     // when add a new condition, the condition's name will be the key to be use
    //     // to access the condition. For some reasons, if the condition name contains
    //     // a "dot" on it ("."), for example adding a condition with name "SALE 35.00"
    //     // this will cause issues when removing this condition by name, this will not be removed
    //     // so when adding a condition, the condition name should not contain any "period" (.)
    //     // to avoid any issues removing it using remove method: removeCartCondition($conditionName);
    //     $cartCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //     ]);
    //     $cartCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 20',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //     ]);
    //     $cartCondition3 = new CartCondition([
    //         'name' => 'Item Less 8%',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-8%',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$cartCondition1, $cartCondition2, $cartCondition3]);

    //     // now lets remove all conditions added in the cart with the type "promo"
    //     $this->cart->removeConditionsByType('promo');

    //     expect($this->cart->getConditions()->count())->toEqual(1, 'We should have 1 condition remaining as promo conditions type has been removed.');
    // });

    // test('add cart condition without condition attributes', function () {
    //     $cartCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$cartCondition1]);

    //     // prove first we have now the condition on the cart
    //     $contition = $this->cart->getCondition('SALE 5%');
    //     expect($contition->getName())->toEqual('SALE 5%');

    //     // when get attribute is called and there is no attributes added,
    //     // it should return an empty array
    //     $conditionAttribute = $contition->getAttributes();
    //     expect($conditionAttribute)->toBeArray();
    // });

    // test('add cart condition with condition attributes', function () {
    //     $cartCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //         'attributes' => [
    //             'description' => 'october fest promo sale',
    //             'sale_start_date' => '2015-01-20',
    //             'sale_end_date' => '2015-01-30',
    //         ],
    //     ]);

    //     $item = [
    //         'id' => 456,
    //         'name' => 'Sample Item 1',
    //         'price' => 100,
    //         'quantity' => 1,
    //         'attributes' => [],
    //     ];

    //     $this->cart->add($item);

    //     $this->cart->condition([$cartCondition1]);

    //     // prove first we have now the condition on the cart
    //     $contition = $this->cart->getCondition('SALE 5%');
    //     expect($contition->getName())->toEqual('SALE 5%');

    //     // when get attribute is called and there is no attributes added,
    //     // it should return an empty array
    //     $conditionAttributes = $contition->getAttributes();
    //     expect($conditionAttributes)->toBeArray();
    //     expect($conditionAttributes)->toHaveKey('description');
    //     expect($conditionAttributes)->toHaveKey('sale_start_date');
    //     expect($conditionAttributes)->toHaveKey('sale_end_date');
    //     expect($conditionAttributes['description'])->toEqual('october fest promo sale');
    //     expect($conditionAttributes['sale_start_date'])->toEqual('2015-01-20');
    //     expect($conditionAttributes['sale_end_date'])->toEqual('2015-01-30');
    // });

    // test('get order from condition', function () {
    //     $cartCondition1 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //         'order' => 2,
    //     ]);
    //     $cartCondition2 = new CartCondition([
    //         'name' => 'Item Gift Pack 20',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //         'order' => '3',
    //     ]);
    //     $cartCondition3 = new CartCondition([
    //         'name' => 'Item Less 8%',
    //         'type' => 'tax',
    //         'target' => 'total',
    //         'value' => '-8%',
    //         'order' => 'first',
    //     ]);

    //     expect($cartCondition1->getOrder())->toEqual(2);
    //     expect($cartCondition2->getOrder())->toEqual(3);
    //     // numeric string is converted to integer
    //     expect($cartCondition3->getOrder())->toEqual(0);

    //     // no numeric string is converted to 0
    //     $this->cart->condition($cartCondition1);
    //     $this->cart->condition($cartCondition2);
    //     $this->cart->condition($cartCondition3);

    //     $conditions = $this->cart->getConditions();

    //     expect($conditions->shift()->getType())->toEqual('sale');
    //     expect($conditions->shift()->getType())->toEqual('promo');
    //     expect($conditions->shift()->getType())->toEqual('tax');
    // });

    // test('condition ordering', function () {
    //     $cartCondition1 = new CartCondition([
    //         'name' => 'TAX',
    //         'type' => 'tax',
    //         'target' => 'total',
    //         'value' => '-8%',
    //         'order' => 5,
    //     ]);
    //     $cartCondition2 = new CartCondition([
    //         'name' => 'SALE 5%',
    //         'type' => 'sale',
    //         'target' => 'total',
    //         'value' => '-5%',
    //         'order' => 2,
    //     ]);
    //     $cartCondition3 = new CartCondition([
    //         'name' => 'Item Gift Pack 20',
    //         'type' => 'promo',
    //         'target' => 'total',
    //         'value' => '-25',
    //         'order' => 1,
    //     ]);

    //     $this->cart->condition($cartCondition1);
    //     $this->cart->condition($cartCondition2);
    //     $this->cart->condition($cartCondition3);

    //     expect($this->cart->getConditions()->first()->getName())->toEqual('Item Gift Pack 20');
    //     expect($this->cart->getConditions()->last()->getName())->toEqual('TAX');
    // });

});

describe('conditions', function () {
    test('can be completely cleared', function () {
        $siteWideDiscount = new CartCondition([
            'name' => 'Site Wide Discount',
            'type' => 'discount',
            'target' => 'total',
            'value' => '-5%',
        ]);

        $giftCard = new CartCondition([
            'name' => 'Gift Card',
            'type' => 'gift_card',
            'target' => 'total',
            'value' => '-25',
        ]);

        $itemCondition = new CartCondition([
            'name' => 'Item Discount',
            'type' => 'discount',
            'value' => '-5%',
        ]);

        $item = [
            'id' => 1,
            'name' => 'Backpack',
            'price' => 168.72,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [$itemCondition],
        ];

        $this->cart->add($item);

        $this->cart->condition([$siteWideDiscount, $giftCard]);

        expect($this->cart->getConditions()->count())->toEqual(2, 'Cart should have two conditions');
        expect($this->cart->get(1)['conditions'])->toHaveCount(1, 'Cart should have one condition');

        $this->cart->clearAllConditions();

        expect($this->cart->getConditions()->count())->toEqual(0, 'Cart should have no conditions now');
        expect($this->cart->get(1)['conditions'])->toHaveCount(0, 'Cart items should have no conditions now');
    });
});
