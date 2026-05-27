<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Testing;

use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Coupons\Coupon;

class CartFactory
{
    public function __construct(private Cart $cart) {}

    public function withItems(int $count): static
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->cart->add($i, "Test Item {$i}", 10.00, 1);
        }

        return $this;
    }

    public function withCondition(CartCondition $condition): static
    {
        $this->cart->condition($condition);

        return $this;
    }

    public function withCoupon(Coupon $coupon): static
    {
        $this->cart->coupon($coupon);

        return $this;
    }
}
