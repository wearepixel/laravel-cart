<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Shipping;

use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Contracts\CartConditionable;
use Wearepixel\Cart\Exceptions\InvalidShippingRateException;

abstract class ShippingRate implements CartConditionable
{
    protected string $name;

    protected string $value;

    protected string $target = 'total';

    protected ?float $minimum = null;

    protected ?float $maximum = null;

    abstract public function isApplicable(): bool;

    public function toCondition(): CartCondition
    {
        if (! $this->isApplicable()) {
            throw new InvalidShippingRateException("Shipping rate [{$this->name}] is not applicable.");
        }

        $data = [
            'name'   => $this->name,
            'type'   => 'shipping',
            'value'  => $this->value,
            'target' => $this->target,
        ];

        if ($this->minimum !== null) {
            $data['minimum'] = $this->minimum;
        }

        if ($this->maximum !== null) {
            $data['maximum'] = $this->maximum;
        }

        return new CartCondition($data);
    }
}
