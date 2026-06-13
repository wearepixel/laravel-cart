<?php

declare(strict_types=1);

use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Shipping\ShippingRate;
use Wearepixel\Cart\Exceptions\InvalidShippingRateException;

class FlatRateShipping extends ShippingRate
{
    protected string $name = 'Standard Shipping';
    protected string $value = '+10';

    public function isApplicable(): bool
    {
        return true;
    }
}

class FreeShipping extends ShippingRate
{
    protected string $name = 'Free Shipping';
    protected string $value = '+0';

    public function isApplicable(): bool
    {
        return false;
    }
}

describe('ShippingRate', function () {
    test('converts to a CartCondition', function () {
        $condition = (new FlatRateShipping)->toCondition();

        expect($condition)->toBeInstanceOf(CartCondition::class);
        expect($condition->getName())->toBe('Standard Shipping');
        expect($condition->getValue())->toBe('+10');
        expect($condition->getType())->toBe('shipping');
        expect($condition->getTarget())->toBe('total');
    });

    test('isApplicable() returning false causes toCondition() to throw', function () {
        expect(fn() => (new FreeShipping)->toCondition())
            ->toThrow(InvalidShippingRateException::class);
    });

    test('supports subtotal as target', function () {
        $rate = new class extends ShippingRate {
            protected string $name = 'Express';
            protected string $value = '+25';
            protected string $target = 'subtotal';
            public function isApplicable(): bool { return true; }
        };

        expect($rate->toCondition()->getTarget())->toBe('subtotal');
    });

    test('supports maximum free shipping threshold', function () {
        $rate = new class extends ShippingRate {
            protected string $name = 'Conditional Shipping';
            protected string $value = '+15';
            protected ?float $maximum = 100.0;
            public function isApplicable(): bool { return true; }
        };

        expect($rate->toCondition()->getMaximum())->toBe(100.0);
    });
});
