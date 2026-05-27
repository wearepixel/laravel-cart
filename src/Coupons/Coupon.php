<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Coupons;

use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\Contracts\CartConditionable;
use Wearepixel\Cart\Exceptions\InvalidCouponException;

abstract class Coupon implements CartConditionable
{
    protected string $code;

    protected string $value;

    protected string $target = 'subtotal';

    protected ?float $minimum = null;

    protected ?float $maximum = null;

    abstract public function isValid(): bool;

    public function getCode(): string
    {
        return $this->code;
    }

    public function toCondition(): CartCondition
    {
        if (! $this->isValid()) {
            throw new InvalidCouponException("Coupon [{$this->code}] is not valid.");
        }

        $data = [
            'name'   => $this->code,
            'type'   => 'coupon',
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
