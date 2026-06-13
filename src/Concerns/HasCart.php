<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Concerns;

use Wearepixel\Cart\Cart;

trait HasCart
{
    public array $cartItems = [];

    public float $cartTotal = 0.0;

    public float $cartSubTotal = 0.0;

    public int $cartCount = 0;

    protected function getCart(): Cart
    {
        return app('cart');
    }

    public function refreshCart(): void
    {
        $cart = $this->getCart();

        $this->cartItems = $cart->getContent()->toArray();
        $this->cartSubTotal = (float) $cart->getSubTotal(false);
        $this->cartTotal = (float) $cart->getTotal();
        $this->cartCount = (int) $cart->getTotalQuantity();
    }

    public function cartAdd(
        int|string $id,
        string $name,
        float $price,
        int $quantity = 1,
        array $attributes = [],
    ): void {
        $this->getCart()->add($id, $name, $price, $quantity, $attributes);
        $this->refreshCart();
    }

    public function cartRemove(int|string $id): void
    {
        $this->getCart()->remove($id);
        $this->refreshCart();
    }

    public function cartUpdate(int|string $id, array $data): void
    {
        $this->getCart()->update($id, $data);
        $this->refreshCart();
    }

    public function cartClear(): void
    {
        $this->getCart()->clear();
        $this->refreshCart();
    }
}
