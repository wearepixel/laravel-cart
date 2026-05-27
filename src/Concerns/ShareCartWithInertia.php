<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Concerns;

use Wearepixel\Cart\Cart;

class ShareCartWithInertia
{
    public static function data(?Cart $cart = null): array
    {
        $cart ??= app('cart');

        return [
            'items'    => $cart->getContent()->toArray(),
            'subtotal' => (float) $cart->getSubTotal(false),
            'total'    => (float) $cart->getTotal(),
            'count'    => (int) $cart->getTotalQuantity(),
        ];
    }
}
