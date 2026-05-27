<?php

use Wearepixel\Cart\Commands\MakeShippingCommand;

describe('MakeShippingCommand', function () {
    test('stub contains the given class name', function () {
        $stub = MakeShippingCommand::stub('FlatRateShipping');
        expect($stub)->toContain('class FlatRateShipping extends ShippingRate');
    });

    test('stub uses the App\\Cart\\Shipping namespace', function () {
        $stub = MakeShippingCommand::stub('FlatRateShipping');
        expect($stub)->toContain('namespace App\\Cart\\Shipping');
    });

    test('stub imports the ShippingRate base class', function () {
        $stub = MakeShippingCommand::stub('FlatRateShipping');
        expect($stub)->toContain('use Wearepixel\\Cart\\Shipping\\ShippingRate');
    });

    test('stub declares isApplicable() method', function () {
        $stub = MakeShippingCommand::stub('FlatRateShipping');
        expect($stub)->toContain('public function isApplicable(): bool');
    });

    test('stub defaults target to total', function () {
        $stub = MakeShippingCommand::stub('FlatRateShipping');
        expect($stub)->toContain("'total'");
    });
});
