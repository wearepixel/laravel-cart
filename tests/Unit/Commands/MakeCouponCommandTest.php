<?php

use Wearepixel\Cart\Commands\MakeCouponCommand;

describe('MakeCouponCommand', function () {
    test('stub contains the given class name', function () {
        $stub = MakeCouponCommand::stub('TenPercentCoupon');
        expect($stub)->toContain('class TenPercentCoupon extends Coupon');
    });

    test('stub uses the App\\Cart\\Coupons namespace', function () {
        $stub = MakeCouponCommand::stub('TenPercentCoupon');
        expect($stub)->toContain('namespace App\\Cart\\Coupons');
    });

    test('stub imports the Coupon base class', function () {
        $stub = MakeCouponCommand::stub('TenPercentCoupon');
        expect($stub)->toContain('use Wearepixel\\Cart\\Coupons\\Coupon');
    });

    test('stub declares isValid() method', function () {
        $stub = MakeCouponCommand::stub('TenPercentCoupon');
        expect($stub)->toContain('public function isValid(): bool');
    });

    test('stub uses strict_types', function () {
        $stub = MakeCouponCommand::stub('TenPercentCoupon');
        expect($stub)->toContain('declare(strict_types=1)');
    });
});
