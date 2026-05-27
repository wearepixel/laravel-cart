<a name="top"></a>
[![Laravel Cart](./docs/laravel-cart.png)](https://joelmale.com)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/wearepixel/laravel-cart.svg?style=flat-square)](https://packagist.org/packages/wearepixel/laravel-cart)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/wearepixel/laravel-cart/tests.yml?branch=master&label=Tests)](https://github.com/wearepixel/laravel-cart/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/wearepixel/laravel-cart.svg?style=flat-square)](https://packagist.org/packages/wearepixel/laravel-cart)
[![GitHub last commit](https://img.shields.io/github/last-commit/wearepixel/laravel-cart)](#)
[![License](https://poser.pugx.org/wearepixel/laravel-cart/license.svg)](https://packagist.org/packages/wearepixel/laravel-cart)
[![Free](https://img.shields.io/badge/free_for_non_commercial_use-brightgreen)](#-license)

A Cart Implementation for Laravel.

Supported Laravel Versions: 10, 11, 12, and 13.

For Laravel 9.0 and below, please use version [1.0](https://github.com/wearepixel/laravel-cart/releases/tag/1.0.12)

## Table of Contents

- [Getting Started](#-getting-started)
- [Demo](#-deni)
- [Documentation](#-documentation)
  - [Configuration](#configuration)
  - [Basic Usage](#basic-usage)
  - [Conditions](#conditions)
  - [Cart Items](#cart-items)
  - [Database Support](#database-support)
  - [Events](#events)
- [Credits](#-credits)
- [License](#-license)

## 🚀 Getting Started

### 🔥 Installing

Install the package through [Composer](http://getcomposer.org/).

`composer require wearepixel/laravel-cart`

## 🧑‍🍳 Demo

```php
// Add an item to the cart
\Cart::add(
    1, // any unique id
    'Product 1', // product name
    19.99, // product price
    2, // quantity
    ['size' => 'large'] // an array of extra attributes
);

// get the entire cart
$cartContents = \Cart::getContent();

// update an item already in the cart
\Cart::update(
    1, // the same unique id that was used to add the item
    ['quantity' => 3] // the quantity to update
);

// remove the item by its id
\Cart::remove(1);

// get the total of the cart
$total = Cart::getTotal();

// clear it all when you've finished (like when you've stored the order)
\Cart::clear();
```

## 📚 Documentation

### ⚙️ Configuration

You can publish the configuration file to customize various options.

```php
php artisan vendor:publish --provider="Wearepixel\Cart\CartServiceProvider" --tag="config"
```

#### Formatting Numbers

The package by default does not use round to format numbers, and instead returns the number using floatval().

If you'd prefer this number to be rounded, you can customize the formatting in the configuration file.

Defaults to false.

```php
'format_numbers' => env('LARAVEL_CART_FORMAT_VALUES', false),
'round_mode' => env('LARAVEL_CART_ROUND_MODE', 'down'),
```

#### Decimals

You can customize the number of decimals in the configuration file.

Defaults to 2.

```php
'decimals' => env('LARAVEL_CART_DECIMALS', 2),
```

#### Round Mode

The package uses the `round` function to round the prices. You can customize the rounding mode in the configuration file.

Defaults to `down`.

```php
'round_mode' => env('LARAVEL_CART_ROUND_MODE', 'down'),
```

### Basic Usage

The cart has a default sessionKey that holds the cart data and stores it in the session, so you can have multiple carts for multiple users.

This also serves as a cart unique identifier which you can use to bind a cart to a specific user if you want to.

Make sure to call `\Cart::setSessionKey($sessionKey)` before calling any other cart methods.

Usually this is not required.

```php
// Binds the cart to a unique id (user id, session id, etc.)
\Cart::setSessionKey(User::first()->id);
```

#### Adding to the cart: **Cart::add()**

There are a few ways to add items to the cart.

```php
// Add a simple product to the cart
Cart::add(
    455, # product id
    'Sample Item', # product name
    100.99, # product price
    2, # quantity
    [] # optional attributes
);

// array format
Cart::add([
    456, # product id
    'Leather Shoes', # product name
    187, # product price
    1, # quantity
    [] # optional attributes
]);


// add an item with attributes
Cart::add([
    457, // product id
    'T-Shirt', // product name
    29.99, // product price
    1, // quantity
    [
        'size' => 'L',
        'color' => 'Blue'
    ] // attributes
]);

// add an item with conditions
Cart::add([
    458, // product id
    'Headphones', // product name
    199.99, // product price
    1, // quantity
    [], // attributes
    [
        [
            'name' => '10% Off',
            'type' => 'discount',
            'value' => '-10%'
        ]
    ] // conditions
]);

// add multiple items at one time
Cart::add(
    [
        456, # product id
        'Leather Shoes', # product name
        187, # product price
        1, # quantity
        [] # optional attributes
    ],
    [
        431, # product id
        'Leather Jacket', # product name
        254.50, # product price
        1, # quantity
        [] # optional attributes
    ]
);
```

#### Updating an item on a cart: **Cart::update()**

```php
Cart::update(
    456, # product id
    [
        'name' => 'New Item Name', // new item name
        'price' => 98.67, // new item price as a float or string
    ]
);

// updating a product's quantity
Cart::update(
    456, # product id
    [
    'quantity' => 2, // by default adding the quantity (so if from 4 to 6)
    ]
);

// reducing it...
Cart::update(
    456,
    [
        'quantity' => -1, // so if from 4 to 3
    ]
);

// you can replace the quantity by setting relative to false
Cart::update(
    456, # product id
    [
        'quantity' => [
            'relative' => false,
            'value' => 5 // if the quantity was 2, it will now be 5
        ],
    ]
);
```

#### Removing an item on a cart: **Cart::remove()**

```php
// Remove an item from the cart by its id
Cart::remove(456);
```

#### Getting an item on a cart: **Cart::get()**

```php
// Get an item from the cart by its id
Cart::get(456);

// You can also get the total price of the item
$summedPrice = Cart::get($itemId)->getPriceSum();
```

#### Getting the cart content: **Cart::getContent()**

```php
// Returns a collection of the cart's contents
$cartData = Cart::getContent();

// Gets the total number of items (not quantity) in the cart
$cartCollection->count();

// Transform the collection to an array or a JSON
$cartCollection->toArray();
$cartCollection->toJson();
```

Get cart total quantity: **Cart::getTotalQuantity()**

```php
$cartTotalQuantity = Cart::getTotalQuantity();
```

#### Cart subtotal: **Cart::getSubTotal()**

```php
$subTotal = Cart::getSubTotal();
```

#### Cart subtotal without conditions: **Cart::getSubTotalWithoutConditions()**

```php
$subTotalWithoutConditions = Cart::getSubTotalWithoutConditions();
```

Cart Total: **Cart::getTotal()**

```php
$total = Cart::getTotal();
```

#### Check if cart is empty: **Cart::isEmpty()**

```php
Cart::isEmpty();
```

#### Clearing the Cart: **Cart::clear()**

This clears all items and conditions from the cart.

```php
Cart::clear();
```

#### Clearing the cart items only: **Cart::clearItems()**

This clears all items, but keeps the conditions (useful for when you want to keep the conditions but remove the items)

```php
Cart::clearItems();
```

### Conditions

Conditions are very useful for adding things like discounts, taxes, shipping, etc.

Conditions can be added on the entire cart or on individual items, and can even be applied only at certain cart values.

Conditions on a cart level should always have a `target` of `subtotal` or `total`. This tells the cart which value to apply the condition to.

You can provide an optional `minimum` value which should be the dollar value in which the target (subtotal or total) needs to be for the condition to be active and impact the cart.

You can also provide an `order` to cart conditions which tells the cart in what order to apply the conditions. Item level conditions do not support the `order` parameter.

#### Conditions on the cart

##### Adding a condition to the cart: **Cart::condition()**

```php
// Add a single condition to the cart
$condition = new \Wearepixel\Cart\CartCondition([
    'name' => 'Tax: 10%',
    'type' => 'tax',
    'target' => 'subtotal', // this condition will be applied to cart's subtotal when getSubTotal() is called.
    'value' => '10%',
    'attributes' => [ // add extra attributes here
    	'description' => 'Compulsory tax',
    ]
]);

Cart::condition($condition);

// Add multiple conditions
$tax = new \Wearepixel\Cart\CartCondition([
    'name' => 'Tax: 10%',
    'type' => 'tax',
    'target' => 'subtotal', // this condition will be applied to cart's subtotal when getSubTotal() is called.
    'value' => '10%',
    'order' => 2
]);

$shipping = new \Wearepixel\Cart\CartCondition([
    'name' => 'Shipping: $15',
    'type' => 'shipping',
    'target' => 'subtotal', // this condition will be applied to cart's subtotal when getSubTotal() is called.
    'value' => '+15',
    'order' => 1
]);

Cart::condition($tax);
Cart::condition($shipping);

// or as an array
Cart::condition([$tax, $shipping]);

// add condition to only apply on totals, not in subtotal
$shipping = new \Wearepixel\Cart\CartCondition([
    'name' => 'Express Shipping $15',
    'type' => 'shipping',
    'target' => 'total',
    'value' => '+15',
    'order' => 1
]);

Cart::condition($shipping);
```

##### Getting conditions on the cart: **Cart::getConditions()**

```php
// To get all applied conditions on a cart, use below:
$cartConditions = Cart::getConditions();

foreach($cartConditions as $condition)
{
    $condition->getTarget(); // the target of which the condition was applied
    $condition->getName(); // the name of the condition
    $condition->getType(); // the type
    $condition->getValue(); // the value of the condition
    $condition->getOrder(); // the order of the condition
    $condition->getMinimum(); // the minimum dollar amount of the target, needed to activate the condition
    $condition->getMaximum(); // the maximum dollar amount of the target, needed to keep the condition active
    $condition->getAttributes(); // the attributes of the condition, returns an empty [] if no attributes added
}
```

##### Getting conditions on the cart as an array: **Cart::getConditions(array: true)**

You can get all cart conditions in array format by passing "array: true". This is useful if you want to store the carts conditions on a Livewire component since by default we have collections inside collections for conditions which Livewire does not support.

```php
$cartConditions = Cart::getConditions(true);
$cartConditions = Cart::getConditions(active: true);

foreach ($cartConditions as $condition) {
    $condition['name']; // the name of the condition
    $condition['type']; // the type
    $condition['value']; // the value of the condition
    $condition['order']; // the order of the condition
    $condition['minimum']; // the minimum dollar amount of the target, needed to activate the condition
    $condition['maximum']; // the maximum dollar amount of the target, needed to keep the condition active
    $condition['attributes']; // the attributes of the condition, returns an empty [] if no attributes added
}
```

##### Getting conditions by name: **Cart::getCondition($conditionName)**

```php
$condition = Cart::getCondition('GST');

$condition->getTarget(); // the target of which the condition was applied
$condition->getName(); // the name of the condition
$condition->getType(); // the type
$condition->getValue(); // the value of the condition
$condition->getMinimum(); // the minimum dollar amount of the target, needed to activate the condition
$condition->getMaximum(); // the maximum dollar amount of the target, needed to keep the condition active
$condition->getAttributes(); // the attributes of the condition, returns an empty [] if no attributes added
```

##### Getting active conditions on the cart: **Cart::getConditions(active: true)**

You can get only active conditions by passsing `active: true` to the `getConditions()` method.

This will return conditions that are actively being applied to the cart (i.e if they meet their minimum or maximum value)

```php
$tenPercentOff = new CartCondition([
    'name' => '10% Off',
    'type' => 'discount',
    'target' => 'subtotal',
    'value' => '-10%',
    'minimum' => 120,
    'order' => 1,
]);

Cart::getConditions(active: true);

// will return "10% Off" if the subtotal of the cart is $200.
// will return no conditions if the subtotal is $100.

$shipping = new CartCondition([
    'name' => 'Shipping',
    'type' => 'discount',
    'target' => 'subtotal',
    'value' => '10',
    'maximum' => 200,
    'order' => 1,
]);

Cart::getConditions(active: true);

// will return "Shipping" if the subtotal of the cart is less than or equal 200
// will return no conditions if the subtotal is $210
```

##### Calculating condition value

There are 2 ways to calculate the value of a condition:

1. Using the `getCalculatedValue` method on the condition instance
2. Using the `getCalculatedValueForCondition` method on the cart instance and passing the condition name

##### Using the `getCalculatedValue` method on the condition instance

```php
$subTotal = Cart::getSubTotal();
$condition = Cart::getCondition('10% GST');
$conditionCalculatedValue = $condition->getCalculatedValue($subTotal);
```

##### Using the `getCalculatedValueForCondition` method on the cart instance

This method automatically calculates the value of a condition by it's name based on the order of the conditions.

```php
Cart::add([
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

Cart::getCalculatedValueForCondition('Coupon Discount'); // returns 200
Cart::getCalculatedValueForCondition('Gift Card'); // returns 0 as the coupon discount is applied first and brings the subtotal to 0
```

##### Adding conditions that activate once a minimum value is met

You can add a `minimum` amount required for a condition to activate.

This is useful for applying discounts only after certain cart values, i.e: 10% off for any purchases over $120.

```php
$tenPercentOff = new CartCondition([
    'name' => '10% Off',
    'type' => 'discount',
    'target' => 'subtotal',
    'value' => '-10%',
    'minimum' => 120,
    'order' => 1,
]);

Cart::condition($tenPercentOff)
```

##### Adding conditions that only activate up to a maximum value

You can add a `maximum` amount required for a condition to activate.

This is useful for applying discounts up until amounts, i.e shipping for anything below $200, and free shipping above.

```php
$shipping = new CartCondition([
    'name' => 'Shipping',
    'type' => 'discount',
    'target' => 'subtotal',
    'value' => '12',
    'maximum' => 200,
    'order' => 1,
]);

Cart::condition($shipping)
```

#### Conditions on items

Item conditions are useful if you have discounts to be applied specifically on an item and not on the whole cart value.

```php

// lets create first our condition instance
$saleCondition = new \Wearepixel\Cart\CartCondition([
    'name' => '50% Off',
    'type' => 'tax',
    'value' => '-50%',
]);

// Create the product data with the condition
$product = [
    'id' => 456,
    'name' => 'Sample Item 1',
    'price' => 100,
    'quantity' => 1,
    'attributes' => [],
    'conditions' => $saleCondition
];

// Now add the product to the cart
Cart::add($product);

// You can of course also do multiple conditions on an item
$saleCondition = new \Wearepixel\Cart\CartCondition([
    'name' => 'SALE 5%',
    'type' => 'sale',
    'value' => '-5%',
]);

$discountCode = new CartCondition([
    'name' => 'Discount Code',
    'type' => 'promo',
    'value' => '-25',
]);

$item = [
    'id' => 456,
    'name' => 'Sample Item 1',
    'price' => 100,
    'quantity' => 1,
    'attributes' => [],
    'conditions' => [$saleCondition, $discountCode]
];

Cart::add($item);
```

> NOTE: All cart per-item conditions should be added before calling **Cart::getSubTotal()**

Then Finally you can call **Cart::getSubTotal()** to get the Cart sub total with the applied conditions on each of the items.

```php
// the subtotal will be calculated based on the conditions added that has target => "subtotal"
// and also conditions that are added on per item
$cartSubTotal = Cart::getSubTotal();
```

##### Add a condition to an existing item on the cart: **Cart::addItemCondition($productId, $itemCondition)**

Adding Condition to an existing Item on the cart is simple as well.

```php
$condition = new CartCondition([
    'name' => 'COUPON 101',
    'type' => 'coupon',
    'value' => '-5%',
]);

Cart::addItemCondition(456, $condition);
```

#### Clearing Cart Conditions: **Cart::clearCartConditions()**

This clears all cart level conditions, and does not affect item level conditions.

```php
Cart::clearCartConditions()
```

If you wish to clear all conditions from all items and the cart, use **Cart::clearAllConditions()**

```php
Cart::clearAllConditions()
```

##### Remove a specific cart condtion: **Cart::removeCartCondition($conditionName)**

```php
Cart::removeCartCondition('Summer Sale 5%')
```

##### Remove a specific item condition: **Cart::removeItemCondition($itemId, $conditionName)**

```php
Cart::removeItemCondition(456, 'SALE 5%')
```

##### Clear all item conditions: **Cart::clearItemConditions($itemId)**

```php
Cart::clearItemConditions(456)
```

#### Get conditions by type: **Cart::getConditionsByType($type)**

This returns all conditions that has been added to the cart by the type specified.

```php
$tax = Cart::getConditionsByType('tax');
```

##### Remove conditions by type: **Cart::removeConditionsByType($type)**

```php
Cart::removeConditionsByType('tax');
```

### Cart Items

The method **Cart::getContent()** returns a collection of items.

Apart from the above methods, you can also get the price of an item with or without **item level conditions** applied.

These methods do not apply cart level conditions.

```php
// With no conditions, just the price * quantity
$item->getPriceSum();

// With conditions applied get the price of a single quantity
$item->getPriceWithConditions();

// Get the sum with conditions applied
$item->getPriceSumWithConditions();

// Without conditions applied
$item->getPriceSumWithConditions();
```

### Storage Options

By default the cart is stored in the session, but there are times you may want to store the cart in the database.

For instance, you may want to store the cart in the database so that the cart can be retrieved even after the user logs out or closes the browser, or you may want to add cart timeouts and support for multiple computers.

#### Session

The cart is stored in the session by default, using Laravel's in-built SessionManager.

#### Database Support

To get started, you'll need to create a new model for your Cart, the package requires only 3 columns, but you're free to extend this as you wish and add more columns.

You can utilise the [events](#events) provided by the package to store additional information alongside the cart.

```php
$table->string('session_id'); // this handles the session id of the cart
$table->text('items'); // this will store the cart items
$table->text('conditions'); // this will store the cart level conditions
```

Then add some json casts to your model and fillable columns:

```php
protected $guarded = [];

protected $casts = [
    'items' => 'array',
    'conditions' => 'array',
];
```

Then update the configuration file to use the database driver:

```php
'driver' => 'database',

'storage' => [
    'session',
    'database' => [
        'model' => \App\Models\Cart::class, // your model here
        'id' => 'session_id',
        'items' => 'items',
        'conditions' => 'conditions',
    ],
],
```

### Events

The package provides a few events that you can listen to in order to manipulate the cart or take actions based on cart events.

#### LaravelCart.Created

This fires every time a cart is instantiated (i.e every time \Cart::add() is called)

```
Event::listen('LaravelCart.Added', function () {
    // cart was created
});
```

#### LaravelCart.Adding

This fires every time an item is being added to the cart

```
Event::listen('LaravelCart.Adding', function ($item) {
    // item is being added
});
```

#### LaravelCart.Added

This fires every time an item is successfully added to the cart

```
Event::listen('LaravelCart.Added', function ($item) {
    // item was added
});
```

#### LaravelCart.Updating

This fires every time an item is being updated

```
Event::listen('LaravelCart.Updating', function ($item) {
    // item is being updated
});
```

#### LaravelCart.Updated

This fires every time an item is successfully updated

```
Event::listen('LaravelCart.Updated', function ($item) {
    // item was updated
});
```

#### LaravelCart.Removing

This fires every time an item is being removed

```
Event::listen('LaravelCart.Removing', function ($item) {
    // item is being removed
});
```

#### LaravelCart.Removed

This fires every time an item is successfully removed

```
Event::listen('LaravelCart.Removed', function ($item) {
    // item was removed
});
```

#### LaravelCart.Clearing

This fires every time the cart is being cleared

```
Event::listen('LaravelCart.Clearing', function () {
    // cart is being cleared
});
```

#### LaravelCart.Cleared

This fires every time the cart is successfully cleared

```
Event::listen('LaravelCart.Cleared', function () {
    // cart was cleared
});
```

## 🫡 Credits

This package was orignally created by [darryldecode](https://github.com/darryldecode) but has since seen almost no updates. I have decided to take the old package and transform it into a new package with new features and updates.

## 📓 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
