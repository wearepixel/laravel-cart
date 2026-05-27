<?php

declare(strict_types=1);

use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Coupons\Coupon;
use Wearepixel\Cart\Exceptions\InvalidCouponException;

class AlwaysValidCoupon extends Coupon
{
    protected string $code = 'SAVE10';
    protected string $value = '-10%';

    public function isValid(): bool
    {
        return true;
    }
}

class NeverValidCoupon extends Coupon
{
    protected string $code = 'EXPIRED';
    protected string $value = '-10%';

    public function isValid(): bool
    {
        return false;
    }
}

describe('Coupon', function () {
    test('converts to a CartCondition', function () {
        $condition = (new AlwaysValidCoupon)->toCondition();

        expect($condition)->toBeInstanceOf(CartCondition::class);
        expect($condition->getName())->toBe('SAVE10');
        expect($condition->getValue())->toBe('-10%');
        expect($condition->getType())->toBe('coupon');
        expect($condition->getTarget())->toBe('subtotal');
    });

    test('respects custom target', function () {
        $coupon = new class extends Coupon {
            protected string $code = 'TOTAL10';
            protected string $value = '-10%';
            protected string $target = 'total';
            public function isValid(): bool { return true; }
        };

        expect($coupon->toCondition()->getTarget())->toBe('total');
    });

    test('includes minimum in condition when set', function () {
        $coupon = new class extends Coupon {
            protected string $code = 'BIG10';
            protected string $value = '-10%';
            protected ?float $minimum = 100.0;
            public function isValid(): bool { return true; }
        };

        expect($coupon->toCondition()->getMinimum())->toBe(100.0);
    });

    test('isValid() returning false causes toCondition() to throw', function () {
        expect(fn() => (new NeverValidCoupon)->toCondition())
            ->toThrow(InvalidCouponException::class);
    });

    test('getCode returns the coupon code', function () {
        expect((new AlwaysValidCoupon)->getCode())->toBe('SAVE10');
    });
});
