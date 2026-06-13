<?php

declare(strict_types=1);

use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Tax\TaxRule;
use Wearepixel\Cart\Exceptions\InvalidTaxRuleException;

class AuGst extends TaxRule
{
    protected string $name = 'GST';
    protected string $value = '10%';

    public function isApplicable(): bool
    {
        return true;
    }
}

class NotApplicableTax extends TaxRule
{
    protected string $name = 'VAT';
    protected string $value = '20%';

    public function isApplicable(): bool
    {
        return false;
    }
}

describe('TaxRule', function () {
    test('converts to a CartCondition', function () {
        $condition = (new AuGst)->toCondition();

        expect($condition)->toBeInstanceOf(CartCondition::class);
        expect($condition->getName())->toBe('GST');
        expect($condition->getValue())->toBe('10%');
        expect($condition->getType())->toBe('tax');
        expect($condition->getTarget())->toBe('subtotal');
    });

    test('isApplicable() returning false causes toCondition() to throw', function () {
        expect(fn() => (new NotApplicableTax)->toCondition())
            ->toThrow(InvalidTaxRuleException::class);
    });

    test('supports total as target', function () {
        $tax = new class extends TaxRule {
            protected string $name = 'Sales Tax';
            protected string $value = '8%';
            protected string $target = 'total';
            public function isApplicable(): bool { return true; }
        };

        expect($tax->toCondition()->getTarget())->toBe('total');
    });

    test('supports minimum threshold', function () {
        $tax = new class extends TaxRule {
            protected string $name = 'Luxury Tax';
            protected string $value = '15%';
            protected ?float $minimum = 500.0;
            public function isApplicable(): bool { return true; }
        };

        expect($tax->toCondition()->getMinimum())->toBe(500.0);
    });
});
