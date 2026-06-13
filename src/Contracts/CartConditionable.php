<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Contracts;

use Wearepixel\Cart\CartCondition;

interface CartConditionable
{
    public function toCondition(): CartCondition;
}
