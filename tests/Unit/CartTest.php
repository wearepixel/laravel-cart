<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\Tests\Helpers\MockProduct;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

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

describe('cart', function () {
    test('can add an item', function () {
        $this->cart->add(1, 'Sample Item', 100.99, 2, []);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart content should be 1');
        expect($this->cart->getContent()->first()['id'])->toEqual(1, 'Item added has ID of 1 so first content ID should be 1');
        expect($this->cart->getContent()->first()['price'])->toEqual(100.99, 'Item added has price of 100.99 so first content price should be 100.99');
    });

    test('can add an item in cents', function () {
        $this->cart->add(1, 'Sample Item', 10099, 2, []);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart content should be 1');
        expect($this->cart->getContent()->first()['id'])->toEqual(1, 'Item added has ID of 1 so first content ID should be 1');
        expect($this->cart->getContent()->first()['price'])->toEqual(10099, 'Item added has price of 100.99 so first content price should be 100.99');
    });

    test('can add an item with no price', function () {
        $this->cart->add(455, 'Sample Item', 0, 2, []);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart content should be 1');
        expect($this->cart->getContent()->first()['id'])->toEqual(455, 'Item added has ID of 455 so first content ID should be 455');
        expect($this->cart->getTotal())->toEqual(0, 'Total should be 0');
    });

    test('can add an item without attributes', function () {
        $item = [
            'id' => 1,
            'name' => 'Pocket Shorts',
            'price' => 67.99,
            'quantity' => 2,
        ];

        $this->cart->add($item);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart content should be 1');
        expect($this->cart->getTotal())->toEqual(135.98, 'Cart total should be 135.98');
    });

    test('can add items as array', function () {
        $item = [
            'id' => 456,
            'name' => 'Sample Item',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => [],
        ];

        $this->cart->add($item);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->count())->toEqual(1, 'Cart should have 1 item on it');
        expect($this->cart->getContent()->first()['id'])->toEqual(456, 'The first content must have ID of 456');
        expect($this->cart->getContent()->first()['name'])->toEqual('Sample Item', 'The first content must have name of "Sample Item"');
    });

    test('can add multiple items with an array', function () {
        $items = [
            [
                'id' => 1,
                'name' => 'Half Button Open Collar',
                'price' => 71.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 2,
                'name' => 'Oli Crew Neck',
                'price' => 59.99,
                'quantity' => 1,
                'attributes' => [],
            ],
            [
                'id' => 3,
                'name' => 'Denim Shorts',
                'price' => 69.99,
                'quantity' => 3,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
        expect($this->cart->getContent()->toArray())->toHaveCount(3, 'Cart should have 3 items');
        expect($this->cart->getTotal())->toEqual(341.95, 'Cart should have a total of 341.95');
    });

    test('cart item prices should be normalized when added to cart', function () {
        // add a price in a string format should be converted to float
        $this->cart->add(1, 'Book of Languages Item', '100.99', 2, []);

        expect($this->cart->getContent()->first()['price'])->toBeFloat('Cart price should be a float');
    });

    test('can update a cart item with new attributes and it should be still instance of item attribute collection', function () {
        $item = [
            'id' => 1,
            'name' => 'Leather Shows',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => [
                'product_id' => '145',
                'color' => 'red',
            ],
        ];

        $this->cart->add($item);

        $item = $this->cart->get(1);

        expect($item->attributes)->toBeInstanceOf('Wearepixel\\Cart\\ItemAttributeCollection');

        // now lets update the item with its new attributes
        // when we get that item from cart, it should still be an instance of ItemAttributeCollection
        $updatedItem = [
            'attributes' => [
                'product_id' => '145',
                'color' => 'red',
            ],
        ];

        $this->cart->update(1, $updatedItem);

        expect($item->attributes)->toBeInstanceOf('Wearepixel\\Cart\\ItemAttributeCollection');
    });

    test('item orders are preserved by the order they are added', function () {
        $items = [
            [
                'id' => 50,
                'name' => 'Leather Shows',
                'price' => 67.99,
                'quantity' => 4,
                'attributes' => [
                    'product_id' => '145',
                    'color' => 'red',
                ],
            ],
            [
                'id' => 2,
                'name' => 'Oli Crew Neck',
                'price' => 59.99,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        $items = $this->cart->getContent();

        expect($items->toArray())->toHaveCount(2, 'Cart should have 2 items');
        expect($items->first()->id)->toEqual(50, 'First item should have ID of 1');
        expect($items->last()->id)->toEqual(2, 'Last item should have ID of 2');
    });

    test('item orders are preserved by the order they are added even when items are updated', function () {
        $items = [
            [
                'id' => 50,
                'name' => 'Leather Shows',
                'price' => 67.99,
                'quantity' => 4,
                'attributes' => [
                    'product_id' => '145',
                    'color' => 'red',
                ],
            ],
            [
                'id' => 2,
                'name' => 'Oli Crew Neck',
                'price' => 59.99,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        $this->cart->update(50, ['name' => 'Updated Name']);

        $items = $this->cart->getContent();

        expect($items->toArray())->toHaveCount(2, 'Cart should have 2 items');
        expect($items->first()->id)->toEqual(50, 'First item should have ID of 1');
        expect($items->last()->id)->toEqual(2, 'Last item should have ID of 2');
    });

    test('can update existing items', function () {
        $items = [
            [
                'id' => 1,
                'name' => 'Magic The Gathering Card Pack',
                'price' => 12.99,
                'quantity' => 3,
                'attributes' => [],
            ],
            [
                'id' => 2,
                'name' => 'Magic The Gathering Card Case',
                'price' => 39.99,
                'quantity' => 1,
                'attributes' => [],
            ],
        ];

        $this->cart->add($items);

        $item = $this->cart->get(1);

        expect($item['name'])->toEqual('Magic The Gathering Card Pack', 'Item name should be "Magic The Gathering Card Pack"');
        expect($item['price'])->toEqual(12.99, 'Item price should be "12.99"');
        expect($item['quantity'])->toEqual(3, 'Item quantity should be 3');

        // when cart's item quantity is updated, the subtotal should be updated as well
        $this->cart->update(1, [
            'name' => 'Magic The Gathering Card Packs',
            'quantity' => 2,
            'price' => 10.20,
        ]);

        $item = $this->cart->get(1);

        expect($item['name'])->toEqual('Magic The Gathering Card Packs', 'Item name should be "Magic The Gathering Card Packs"');
        expect($item['price'])->toEqual(10.20, 'Item price should be 10.20');
        expect($item['quantity'])->toEqual(5, 'Item quantity should be 2');
    });

    test('can update existing item quanties with decimals', function () {
        $items = [
            'id' => 1,
            'name' => 'Fertilizer',
            'price' => 30,
            'quantity' => 7.8,
            'attributes' => [
                'unit' => 'kilogram',
            ],
        ];

        $this->cart->add($items);

        $item = $this->cart->get(1);

        expect($item['quantity'])->toEqual(7.8, 'Item quantity should be 7.8');

        // when cart's item quantity is updated, the subtotal should be updated as well
        $this->cart->update(1, [
            'quantity' => [
                'relative' => true,
                'value' => 2.1,
            ],
        ]);

        $item = $this->cart->get(1);

        expect($item['quantity'])->toEqual(9.9, 'Item quantity should be 9.9');
    });
});

test('cart items attributes', function () {
    $item = [
        'id' => 456,
        'name' => 'Sample Item 1',
        'price' => 67.99,
        'quantity' => 4,
        'attributes' => [
            'size' => 'L',
            'color' => 'blue',
        ],
    ];

    $this->cart->add($item);

    expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
    expect($this->cart->getContent()->first()['attributes'])->toHaveCount(2, 'Item\'s attribute should have two');
    expect($this->cart->getContent()->first()->attributes->size)->toEqual('L', 'Item should have attribute size of L');
    expect($this->cart->getContent()->first()->attributes->color)->toEqual('blue', 'Item should have attribute color of blue');
    expect($this->cart->get(456)->has('attributes'))->toBeTrue('Item should have attributes');
    expect($this->cart->get(456)->get('attributes')->size)->toEqual('L');
});

test('cart update existing item', function () {
    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 3,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 1,
            'attributes' => [],
        ],
    ];

    $this->cart->add($items);

    $itemIdToEvaluate = 456;

    $item = $this->cart->get($itemIdToEvaluate);
    expect($item['name'])->toEqual('Sample Item 1', 'Item name should be "Sample Item 1"');
    expect($item['price'])->toEqual(67.99, 'Item price should be "67.99"');
    expect($item['quantity'])->toEqual(3, 'Item quantity should be 3');

    // when cart's item quantity is updated, the subtotal should be updated as well
    $this->cart->update(456, [
        'name' => 'Renamed',
        'quantity' => 2,
        'price' => 105,
    ]);

    $item = $this->cart->get($itemIdToEvaluate);
    expect($item['name'])->toEqual('Renamed', 'Item name should be "Renamed"');
    expect($item['price'])->toEqual(105, 'Item price should be 105');
    expect($item['quantity'])->toEqual(5, 'Item quantity should be 2');
});

test('cart update existing item with quantity as array and not relative', function () {
    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 3,
            'attributes' => [],
        ],
    ];

    $this->cart->add($items);

    $itemIdToEvaluate = 456;
    $item = $this->cart->get($itemIdToEvaluate);
    expect($item['quantity'])->toEqual(3, 'Item quantity should be 3');

    // now by default when an update takes place and the quantity attribute
    // is present, it will evaluate for arithmetic operation if the quantity
    // should be incremented or decremented, we should also allow the quantity
    // value to be in array format and provide a field if the quantity should not be
    // treated as relative to Item quantity current value
    $this->cart->update($itemIdToEvaluate, ['quantity' => ['relative' => false, 'value' => 5]]);

    $item = $this->cart->get($itemIdToEvaluate);
    expect($item['quantity'])->toEqual(5, 'Item quantity should be 5');
});

test('it removes an item on cart by item id', function () {
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

    $this->cart->add($items);

    $removeItemId = 456;

    $this->cart->remove($removeItemId);

    expect($this->cart->getContent()->toArray())->toHaveCount(2, 'Cart must have 2 items left');
    expect($this->cart->getContent()->has($removeItemId))->toBeFalse('Cart must have not contain the remove item anymore');
});

test('cart sub total', function () {
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

    // if we remove an item, the sub total should be updated as well
    $this->cart->remove(456);

    expect($this->cart->getSubTotal())->toEqual(119.5, 'Cart should have sub total of 119.5');
});

test('sub total when item quantity is updated', function () {
    $this->cart->clear();

    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 3,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 1,
            'attributes' => [],
        ],
    ];

    $this->cart->add($items);

    expect($this->cart->getSubTotal())
        ->toBe(273.22, 'Cart should have sub total of 273.22');

    // when cart's item quantity is updated, the subtotal should be updated as well
    $this->cart->update(456, ['quantity' => 2]);

    expect($this->cart->getSubTotal())->toEqual(409.2, 'Cart should have sub total of 409.2');
});

test('sub total when item quantity is updated by reduced', function () {
    // $this->cart->setConfig(['format_numbers' => true, 'decimals' => 2, 'dec_point' => '.', 'thousands_sep' => ',']);

    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 3,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 1,
            'attributes' => [],
        ],
    ];

    $this->cart->add($items);

    expect($this->cart->getSubTotal())
        ->toBe(273.22, 'Cart should have sub total of 273.22');

    // when cart's item quantity is updated, the subtotal should be updated as well
    $this->cart->update(456, ['quantity' => -1]);

    // get the item to be evaluated
    $item = $this->cart->get(456);

    expect($item['quantity'])->toEqual(2, 'Item quantity of with item ID of 456 should now be reduced to 2');
    expect($this->cart->getSubTotal())->toEqual(205.23, 'Cart should have sub total of 205.23');
});

test('item quantity update by reduced should not reduce if quantity will result to zero', function () {
    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 3,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 1,
            'attributes' => [],
        ],
    ];

    $this->cart->add($items);

    // get the item to be evaluated
    $item = $this->cart->get(456);

    // prove first we have quantity of 3
    expect($item['quantity'])->toEqual(3, 'Item quantity of with item ID of 456 should be reduced to 3');

    // when cart's item quantity is updated, and reduced to more than the current quantity
    // this should not work
    $this->cart->update(456, ['quantity' => -3]);

    expect($item['quantity'])->toEqual(3, 'Item quantity of with item ID of 456 should now be reduced to 2');
});

test('should throw exception when provided invalid values scenario one', function () {
    $this->expectException('Wearepixel\Cart\Exceptions\InvalidItemException');
    $this->cart->add(455, 'Sample Item', 100.99, 0, []);
});

test('should throw exception when provided invalid values scenario two', function () {
    $this->expectException('Wearepixel\Cart\Exceptions\InvalidItemException');
    $this->cart->add('', 'Sample Item', 100.99, 2, []);
});

test('should throw exception when provided invalid values scenario three', function () {
    $this->expectException('Wearepixel\Cart\Exceptions\InvalidItemException');
    $this->cart->add(523, '', 100.99, 2, []);
});

test('clearing cart', function () {
    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 3,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 1,
            'attributes' => [],
        ],
    ];

    $this->cart->add($items);

    expect($this->cart->isEmpty())->toBeFalse('prove first cart is not empty');

    // now let's clear cart
    $this->cart->clear();

    expect($this->cart->isEmpty())->toBeTrue('cart should now be empty');
});

test('cart get total quantity', function () {
    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 3,
            'attributes' => [],
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 1,
            'attributes' => [],
        ],
    ];

    $this->cart->add($items);

    expect($this->cart->isEmpty())->toBeFalse('prove first cart is not empty');

    // now let's count the cart's quantity
    expect($this->cart->getTotalQuantity())->toBeInt('Return type should be INT');
    expect($this->cart->getTotalQuantity())->toEqual(4, 'Cart\'s quantity should be 4.');
});

test('cart can add items as array with associated model', function () {
    $item = [
        'id' => 456,
        'name' => 'Sample Item',
        'price' => 67.99,
        'quantity' => 4,
        'attributes' => [],
        'associatedModel' => MockProduct::class,
    ];

    $this->cart->add($item);

    $addedItem = $this->cart->get($item['id']);

    expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
    expect($this->cart->getContent()->count())->toEqual(1, 'Cart should have 1 item on it');
    expect($this->cart->getContent()->first()['id'])->toEqual(456, 'The first content must have ID of 456');
    expect($this->cart->getContent()->first()['name'])->toEqual('Sample Item', 'The first content must have name of "Sample Item"');
    expect($addedItem->model)->toBeInstanceOf('Wearepixel\Cart\Tests\Helpers\MockProduct');
});

test('cart can add items with multidimensional array with associated model', function () {
    $items = [
        [
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => [],
            'associatedModel' => MockProduct::class,
        ],
        [
            'id' => 568,
            'name' => 'Sample Item 2',
            'price' => 69.25,
            'quantity' => 4,
            'attributes' => [],
            'associatedModel' => MockProduct::class,
        ],
        [
            'id' => 856,
            'name' => 'Sample Item 3',
            'price' => 50.25,
            'quantity' => 4,
            'attributes' => [],
            'associatedModel' => MockProduct::class,
        ],
    ];

    $this->cart->add($items);

    $content = $this->cart->getContent();
    foreach ($content as $item) {
        expect($item->model)->toBeInstanceOf('Wearepixel\Cart\Tests\Helpers\MockProduct');
    }

    expect($this->cart->isEmpty())->toBeFalse('Cart should not be empty');
    expect($this->cart->getContent()->toArray())->toHaveCount(3, 'Cart should have 3 items');
    expect($this->cart->getTotalQuantity())->toBeInt('Return type should be INT');
    expect($this->cart->getTotalQuantity())->toEqual(12, 'Cart\'s quantity should be 4.');
});
