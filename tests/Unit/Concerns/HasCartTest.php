<?php

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\Concerns\HasCart;
use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

class FakeComponent
{
    use HasCart;

    public array $cartItems = [];
    public float $cartTotal = 0.0;
    public float $cartSubTotal = 0.0;
    public int $cartCount = 0;

    public function __construct(private Cart $cart) {}

    protected function getCart(): Cart
    {
        return $this->cart;
    }
}

function makeComponent(): FakeComponent
{
    $events = Mockery::mock('Illuminate\Contracts\Events\Dispatcher');
    $events->shouldReceive('dispatch');

    $cart = new Cart(
        new SessionDriver(new SessionMock, 'TESTKEY'),
        $events,
        'cart',
        require __DIR__ . '/../../Helpers/ConfigMock.php',
    );

    return new FakeComponent($cart);
}

beforeEach(function () {
    $this->component = makeComponent();
});

afterEach(fn() => Mockery::close());

describe('HasCart trait', function () {
    test('refreshCart() populates cartItems', function () {
        $this->component->cartAdd(1, 'Widget', 50.00, 2);

        expect($this->component->cartItems)->not->toBeEmpty();
    });

    test('cartAdd() adds an item and refreshes state', function () {
        $this->component->cartAdd(1, 'Widget', 50.00, 1);

        expect($this->component->cartCount)->toBe(1);
        expect($this->component->cartSubTotal)->toBe(50.00);
    });

    test('cartRemove() removes an item and refreshes state', function () {
        $this->component->cartAdd(1, 'Widget', 50.00, 1);
        $this->component->cartRemove(1);

        expect($this->component->cartCount)->toBe(0);
    });

    test('cartUpdate() updates an item and refreshes state', function () {
        $this->component->cartAdd(1, 'Widget', 50.00, 1);
        $this->component->cartUpdate(1, ['quantity' => ['relative' => false, 'value' => 3]]);

        expect($this->component->cartCount)->toBe(3);
    });

    test('cartClear() empties the cart and refreshes state', function () {
        $this->component->cartAdd(1, 'Widget', 50.00, 1);
        $this->component->cartClear();

        expect($this->component->cartCount)->toBe(0);
        expect($this->component->cartItems)->toBeEmpty();
    });

    test('cartTotal reflects the cart total', function () {
        $this->component->cartAdd(1, 'Widget', 100.00, 1);

        expect($this->component->cartTotal)->toBe(100.00);
    });
});
