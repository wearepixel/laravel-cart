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
        require (__DIR__ . '/../Helpers/ConfigMock.php')
    );

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
});

afterEach(function () {
    Mockery::close();
});

test('subtotal', function () {
    // add condition to subtotal
    $condition = new CartCondition([
        'name' => 'VAT 12.5%',
        'type' => 'tax',
        'target' => 'subtotal',
        'value' => '-5',
    ]);

    $this->cart->condition($condition);

    expect($this->cart->getSubTotal())->toEqual(182.49);

    // the total is also should be the same with sub total since our getTotal
    // also depends on what is the value of subtotal
    expect($this->cart->getTotal())->toEqual(182.49);
});

test('total without condition', function () {
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // no changes in subtotal as the condition's target added was for total
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // total should be the same as subtotal
    expect($this->cart->getTotal())->toEqual(187.49, 'Cart should have a total of 187.49');
});

test('total with condition', function () {
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // add condition
    $condition = new CartCondition([
        'name' => 'VAT 12.5%',
        'type' => 'tax',
        'target' => 'total',
        'value' => '12.5%',
    ]);

    $this->cart->condition($condition);

    // no changes in subtotal as the condition's target added was for total
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // total should be changed
    $this->cart->setDecimals(5);

    expect($this->cart->getTotal())->toEqual(210.92625, 'Cart should have a total of 210.92625');
});

test('total with multiple conditions added scenario one', function () {
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // add condition
    $condition1 = new CartCondition([
        'name' => 'VAT 12.5%',
        'type' => 'tax',
        'target' => 'total',
        'value' => '12.5%',
    ]);

    $condition2 = new CartCondition([
        'name' => 'Express Shipping $15',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '+15',
    ]);

    $this->cart->condition($condition1);
    $this->cart->condition($condition2);

    // no changes in subtotal as the condition's target added was for subtotal
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // total should be changed
    $this->cart->setDecimals(5);
    expect($this->cart->getTotal())->toEqual(225.92625, 'Cart should have a total of 225.92625');
});

test('total with multiple conditions added scenario two', function () {
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // add condition
    $condition1 = new CartCondition([
        'name' => 'VAT 12.5%',
        'type' => 'tax',
        'target' => 'total',
        'value' => '12.5%',
    ]);
    $condition2 = new CartCondition([
        'name' => 'Express Shipping $15',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '-15',
    ]);

    $this->cart->condition($condition1);
    $this->cart->condition($condition2);

    // no changes in subtotal as the condition's target added was for subtotal
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // total should be changed
    $this->cart->setDecimals(5);
    expect($this->cart->getTotal())->toEqual(195.92625, 'Cart should have a total of 195.92625');
});

test('total with multiple conditions added scenario three', function () {
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // add condition
    $condition1 = new CartCondition([
        'name' => 'VAT 12.5%',
        'type' => 'tax',
        'target' => 'total',
        'value' => '-12.5%',
    ]);
    $condition2 = new CartCondition([
        'name' => 'Express Shipping $15',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '-15',
    ]);

    $this->cart->condition($condition1);
    $this->cart->condition($condition2);

    // no changes in subtotal as the condition's target added was for total
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // total should be changed
    $this->cart->setDecimals(5);
    expect($this->cart->getTotal())->toEqual(149.05375, 'Cart should have a total of 149.05375');
});

test('cart multiple conditions can be added once by array', function () {
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // add condition
    $condition1 = new CartCondition([
        'name' => 'VAT 12.5%',
        'type' => 'tax',
        'target' => 'total',
        'value' => '-12.5%',
    ]);
    $condition2 = new CartCondition([
        'name' => 'Express Shipping $15',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '-15',
    ]);

    $this->cart->condition([$condition1, $condition2]);

    // no changes in subtotal as the condition's target added was for total
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // total should be changed
    $this->cart->setDecimals(5);
    expect($this->cart->getTotal())->toEqual(149.05375, 'Cart should have a total of 149.05375');
});

test('total with multiple conditions added scenario four', function () {
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // add condition
    $condition1 = new CartCondition([
        'name' => 'COUPON LESS 12.5%',
        'type' => 'tax',
        'target' => 'total',
        'value' => '-12.5%',
    ]);
    $condition2 = new CartCondition([
        'name' => 'Express Shipping $15',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '+15',
    ]);

    $this->cart->condition($condition1);
    $this->cart->condition($condition2);

    // no changes in subtotal as the condition's target added was for total
    expect($this->cart->getSubTotal())->toEqual(187.49, 'Cart should have sub total of 187.49');

    // total should be changed
    $this->cart->setDecimals(5);
    expect($this->cart->getTotal())->toEqual(179.05375, 'Cart should have a total of 179.05375');
});

test('add item with condition', function () {
    $this->cart->clear();

    $condition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'tax',
        'value' => '-5%',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => $condition1,
    ];

    $this->cart->add($item);

    expect($this->cart->get(456)->getPriceSumWithConditions())->toEqual(95);
    expect($this->cart->getSubTotal())->toEqual(95);
});

test('add item with multiple item conditions in multiple condition instance', function () {
    $this->cart->clear();

    $itemCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'value' => '-5%',
    ]);
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'value' => '-25',
    ]);
    $itemCondition3 = new CartCondition([
        'name' => 'MISC',
        'type' => 'misc',
        'value' => '+10',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => [$itemCondition1, $itemCondition2, $itemCondition3],
    ];

    $this->cart->add($item);

    expect($this->cart->get(456)->getPriceSumWithConditions())->toEqual(80.00, 'Item subtotal with 1 item should be 80');
    expect($this->cart->getSubTotal())->toEqual(80.00, 'Cart subtotal with 1 item should be 80');
});

test('add item with multiple item conditions with target omitted', function () {
    $this->cart->clear();

    // NOTE:
    // $condition1 and $condition4 should not be included in calculation
    // as the target is not for item, remember that when adding
    // conditions in per-item bases, the condition's target should
    // have a value of item
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'value' => '-25',
    ]);
    $itemCondition3 = new CartCondition([
        'name' => 'MISC',
        'type' => 'misc',
        'value' => '+10',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => [$itemCondition2, $itemCondition3],
    ];

    $this->cart->add($item);

    expect($this->cart->get(456)->getPriceSumWithConditions())->toEqual(85.00, 'Cart subtotal with 1 item should be 85');
    expect($this->cart->getSubTotal())->toEqual(85.00, 'Cart subtotal with 1 item should be 85');
});

test('add item condition', function () {
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'value' => '-25',
    ]);
    $coupon101 = new CartCondition([
        'name' => 'COUPON 101',
        'type' => 'coupon',
        'value' => '-5%',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => [$itemCondition2],
    ];

    $this->cart->add($item);

    // let's prove first we have 1 condition on this item
    expect($this->cart->get($item['id'])['conditions'])->toHaveCount(1, 'Item should have 1 condition');

    // now let's insert a condition on an existing item on the cart
    $this->cart->addItemCondition($item['id'], $coupon101);

    expect($this->cart->get($item['id'])['conditions'])->toHaveCount(2, 'Item should have 2 conditions');
});

test('add item condition restrict negative price', function () {
    $condition = new CartCondition([
        'name' => 'Substract amount but prevent negative value',
        'type' => 'promo',
        'value' => '-25',
    ]);

    $item = [
        'id' => 789,
        'name' => 'Sample Item 1',
        'price' => 20,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => [
            $condition,
        ],
    ];

    $this->cart->add($item);

    // Since the product price is 20 and the condition reduces it by 25,
    // check that the item's price has been prevented from dropping below zero.
    expect($this->cart->get($item['id'])->getPriceSumWithConditions())->toEqual(0.00, "The item's price should be prevented from going below zero.");
});

test('get cart condition by condition name', function () {
    $itemCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
    ]);
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$itemCondition1, $itemCondition2]);

    // get a condition applied on cart by condition name
    $condition = $this->cart->getCondition($itemCondition1->getName());

    expect('SALE 5%')->toEqual($condition->getName());
    expect('total')->toEqual($condition->getTarget());
    expect('sale')->toEqual($condition->getType());
    expect('-5%')->toEqual($condition->getValue());
});

test('remove cart condition by condition name', function () {
    $itemCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
    ]);
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$itemCondition1, $itemCondition2]);

    // let's prove first we have now two conditions in the cart
    expect($this->cart->getConditions()->count())->toEqual(2, 'Cart should have two conditions');

    // now let's remove a specific condition by condition name
    $this->cart->removeCartCondition('SALE 5%');

    // cart should have now only 1 condition
    expect($this->cart->getConditions()->count())->toEqual(1, 'Cart should have one condition');
    expect($this->cart->getConditions()->first()->getName())->toEqual('Item Gift Pack 25.00');
});

test('remove item condition by condition name', function () {
    $itemCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'value' => '-5%',
    ]);
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'value' => '-25',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => [$itemCondition1, $itemCondition2],
    ];

    $this->cart->add($item);

    // let's very first the item has 2 conditions in it
    expect($this->cart->get(456)['conditions'])->toHaveCount(2, 'Item should have two conditions');

    // now let's remove a condition on that item using the condition name
    $this->cart->removeItemCondition(456, 'SALE 5%');

    // now we should have only 1 condition left on that item
    expect($this->cart->get(456)['conditions'])->toHaveCount(1, 'Item should have one condition left');
});

test('remove item condition by condition name scenario two', function () {
    // NOTE: in this scenario, we will add the conditions not in array format
    $itemCondition = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'value' => '-5%',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => $itemCondition, // <--not in array format
    ];

    $this->cart->add($item);

    // let's very first the item has 2 conditions in it
    expect($this->cart->get(456)['conditions'])->not->toBeEmpty('Item should have one condition in it.');

    // now let's remove a condition on that item using the condition name
    $this->cart->removeItemCondition(456, 'SALE 5%');

    // now we should have only 1 condition left on that item
    expect($this->cart->get(456)['conditions'])->toBeEmpty('Item should have no condition now');
});

test('clear item conditions', function () {
    $itemCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'value' => '-5%',
    ]);
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'value' => '-25',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
        'conditions' => [$itemCondition1, $itemCondition2],
    ];

    $this->cart->add($item);

    // let's very first the item has 2 conditions in it
    expect($this->cart->get(456)['conditions'])->toHaveCount(2, 'Item should have two conditions');

    // now let's remove all condition on that item
    $this->cart->clearItemConditions(456);

    // now we should have only 0 condition left on that item
    expect($this->cart->get(456)['conditions'])->toHaveCount(0, 'Item should have no conditions now');
});

test('clear cart conditions', function () {
    // NOTE:
    // This only clears all conditions that has been added in a cart bases
    // this does not remove conditions on per item bases
    $itemCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
    ]);
    $itemCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$itemCondition1, $itemCondition2]);

    // let's prove first we have now two conditions in the cart
    expect($this->cart->getConditions()->count())->toEqual(2, 'Cart should have two conditions');

    // now let's clear cart conditions
    $this->cart->clearCartConditions();

    // cart should have now only 1 condition
    expect($this->cart->getConditions()->count())->toEqual(0, 'Cart should have no conditions now');
});

test('get calculated value of a condition', function () {
    $this->cart->clear();

    $cartCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
    ]);
    $cartCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$cartCondition1, $cartCondition2]);

    $subTotal = $this->cart->getSubTotal();

    expect($subTotal)->toEqual(100, 'Subtotal should be 100');

    // way 1
    // now we will get the calculated value of the condition 1
    $cond1 = $this->cart->getCondition('SALE 5%');
    expect($cond1->getCalculatedValue($subTotal))->toEqual(5, 'The calculated value must be 5');

    // way 2
    // get all cart conditions and get their calculated values
    $conditions = $this->cart->getConditions();
    expect($conditions['SALE 5%']->getCalculatedValue($subTotal))->toEqual(5, 'First condition calculated value must be 5');
    expect($conditions['Item Gift Pack 25.00']->getCalculatedValue($subTotal))->toEqual(25, 'First condition calculated value must be 5');
});

test('get conditions by type', function () {
    $cartCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
    ]);
    $cartCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 25.00',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
    ]);
    $cartCondition3 = new CartCondition([
        'name' => 'Item Less 8%',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-8%',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$cartCondition1, $cartCondition2, $cartCondition3]);

    // now lets get all conditions added in the cart with the type "promo"
    $promoConditions = $this->cart->getConditionsByType('promo');

    expect($promoConditions->count())->toEqual(2, 'We should have 2 items as promo condition type.');
});

test('remove conditions by type', function () {
    // NOTE:
    // when add a new condition, the condition's name will be the key to be use
    // to access the condition. For some reasons, if the condition name contains
    // a "dot" on it ("."), for example adding a condition with name "SALE 35.00"
    // this will cause issues when removing this condition by name, this will not be removed
    // so when adding a condition, the condition name should not contain any "period" (.)
    // to avoid any issues removing it using remove method: removeCartCondition($conditionName);
    $cartCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
    ]);
    $cartCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 20',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
    ]);
    $cartCondition3 = new CartCondition([
        'name' => 'Item Less 8%',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-8%',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$cartCondition1, $cartCondition2, $cartCondition3]);

    // now lets remove all conditions added in the cart with the type "promo"
    $this->cart->removeConditionsByType('promo');

    expect($this->cart->getConditions()->count())->toEqual(1, 'We should have 1 condition remaining as promo conditions type has been removed.');
});

test('add cart condition without condition attributes', function () {
    $cartCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$cartCondition1]);

    // prove first we have now the condition on the cart
    $contition = $this->cart->getCondition('SALE 5%');
    expect($contition->getName())->toEqual('SALE 5%');

    // when get attribute is called and there is no attributes added,
    // it should return an empty array
    $conditionAttribute = $contition->getAttributes();
    expect($conditionAttribute)->toBeArray();
});

test('add cart condition with condition attributes', function () {
    $cartCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
        'attributes' => [
            'description' => 'october fest promo sale',
            'sale_start_date' => '2015-01-20',
            'sale_end_date' => '2015-01-30',
        ],
    ]);

    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 100,
        'quantity' => 1,
        'attributes' => [],
    ];

    $this->cart->add($item);

    $this->cart->condition([$cartCondition1]);

    // prove first we have now the condition on the cart
    $contition = $this->cart->getCondition('SALE 5%');
    expect($contition->getName())->toEqual('SALE 5%');

    // when get attribute is called and there is no attributes added,
    // it should return an empty array
    $conditionAttributes = $contition->getAttributes();
    expect($conditionAttributes)->toBeArray();
    expect($conditionAttributes)->toHaveKey('description');
    expect($conditionAttributes)->toHaveKey('sale_start_date');
    expect($conditionAttributes)->toHaveKey('sale_end_date');
    expect($conditionAttributes['description'])->toEqual('october fest promo sale');
    expect($conditionAttributes['sale_start_date'])->toEqual('2015-01-20');
    expect($conditionAttributes['sale_end_date'])->toEqual('2015-01-30');
});

test('get order from condition', function () {
    $cartCondition1 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
        'order' => 2,
    ]);
    $cartCondition2 = new CartCondition([
        'name' => 'Item Gift Pack 20',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
        'order' => '3',
    ]);
    $cartCondition3 = new CartCondition([
        'name' => 'Item Less 8%',
        'type' => 'tax',
        'target' => 'total',
        'value' => '-8%',
        'order' => 'first',
    ]);

    expect($cartCondition1->getOrder())->toEqual(2);
    expect($cartCondition2->getOrder())->toEqual(3);
    // numeric string is converted to integer
    expect($cartCondition3->getOrder())->toEqual(0);

    // no numeric string is converted to 0
    $this->cart->condition($cartCondition1);
    $this->cart->condition($cartCondition2);
    $this->cart->condition($cartCondition3);

    $conditions = $this->cart->getConditions();

    expect($conditions->shift()->getType())->toEqual('sale');
    expect($conditions->shift()->getType())->toEqual('promo');
    expect($conditions->shift()->getType())->toEqual('tax');
});

test('condition ordering', function () {
    $cartCondition1 = new CartCondition([
        'name' => 'TAX',
        'type' => 'tax',
        'target' => 'total',
        'value' => '-8%',
        'order' => 5,
    ]);
    $cartCondition2 = new CartCondition([
        'name' => 'SALE 5%',
        'type' => 'sale',
        'target' => 'total',
        'value' => '-5%',
        'order' => 2,
    ]);
    $cartCondition3 = new CartCondition([
        'name' => 'Item Gift Pack 20',
        'type' => 'promo',
        'target' => 'total',
        'value' => '-25',
        'order' => 1,
    ]);

    $this->cart->condition($cartCondition1);
    $this->cart->condition($cartCondition2);
    $this->cart->condition($cartCondition3);

    expect($this->cart->getConditions()->first()->getName())->toEqual('Item Gift Pack 20');
    expect($this->cart->getConditions()->last()->getName())->toEqual('TAX');
});
