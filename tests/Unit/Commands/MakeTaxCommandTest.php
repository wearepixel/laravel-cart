<?php

use Wearepixel\Cart\Commands\MakeTaxCommand;

describe('MakeTaxCommand', function () {
    test('stub contains the given class name', function () {
        $stub = MakeTaxCommand::stub('GstTaxRule');
        expect($stub)->toContain('class GstTaxRule extends TaxRule');
    });

    test('stub uses the App\\Cart\\Tax namespace', function () {
        $stub = MakeTaxCommand::stub('GstTaxRule');
        expect($stub)->toContain('namespace App\\Cart\\Tax');
    });

    test('stub imports the TaxRule base class', function () {
        $stub = MakeTaxCommand::stub('GstTaxRule');
        expect($stub)->toContain('use Wearepixel\\Cart\\Tax\\TaxRule');
    });

    test('stub declares isApplicable() method', function () {
        $stub = MakeTaxCommand::stub('GstTaxRule');
        expect($stub)->toContain('public function isApplicable(): bool');
    });

    test('stub uses strict_types', function () {
        $stub = MakeTaxCommand::stub('GstTaxRule');
        expect($stub)->toContain('declare(strict_types=1)');
    });
});
