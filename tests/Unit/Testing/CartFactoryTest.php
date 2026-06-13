<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Testing\CartFactory;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

function makeCartForFactory(): Cart
{
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    return new Cart(
        new SessionDriver(new SessionMock, 'TESTKEY'),
        $events,
        'cart',
        require __DIR__ . '/../../Helpers/ConfigMock.php',
    );
}

beforeEach(function () {
    $this->cart = makeCartForFactory();
    $this->factory = new CartFactory($this->cart);
});

afterEach(fn() => Mockery::close());

describe('CartFactory', function () {
    test('withItems adds N generic items to the cart', function () {
        $this->factory->withItems(3);

        expect($this->cart->getContent()->count())->toBe(3);
        expect($this->cart->getTotalQuantity())->toBe(3);
    });

    test('withCondition adds a condition to the cart', function () {
        $condition = new CartCondition(['name' => 'GST', 'type' => 'tax', 'value' => '10%', 'target' => 'subtotal']);
        $this->factory->withCondition($condition);

        expect($this->cart->getCondition('GST'))->not->toBeNull();
    });

    test('methods are chainable', function () {
        $result = $this->factory->withItems(2)->withCondition(
            new CartCondition(['name' => 'Discount', 'type' => 'discount', 'value' => '-5%', 'target' => 'subtotal'])
        );

        expect($result)->toBeInstanceOf(CartFactory::class);
        expect($this->cart->getContent()->count())->toBe(2);
        expect($this->cart->getCondition('Discount'))->not->toBeNull();
    });

    test('withItems generates unique item IDs', function () {
        $this->factory->withItems(3);
        $ids = $this->cart->getContent()->keys()->toArray();

        expect(array_unique($ids))->toHaveCount(3);
    });

    test('consecutive withItems calls produce unique IDs', function () {
        $this->factory->withItems(2)->withItems(2);

        $ids = $this->cart->getContent()->keys()->toArray();
        expect(array_unique($ids))->toHaveCount(4);
        expect($this->cart->getContent()->count())->toBe(4);
    });

    test('withCoupon adds a coupon condition to the cart', function () {
        $coupon = new class extends \Wearepixel\Cart\Coupons\Coupon {
            protected string $code = 'SAVE10';
            protected string $value = '-10%';
            protected string $target = 'subtotal';

            public function isValid(): bool { return true; }
        };

        $this->factory->withCoupon($coupon);

        expect($this->cart->getCondition('SAVE10'))->not->toBeNull();
    });
});
