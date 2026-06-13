<?php

namespace Wearepixel\Cart;

use Illuminate\Support\Collection;
use Wearepixel\Cart\Helpers\Helpers;

/**
 * @property mixed $id
 * @property string $name
 * @property float|int $price
 * @property int|float $quantity
 * @property array<string, mixed> $attributes
 * @property mixed $conditions
 */
class ItemCollection extends Collection
{
    /**
     * Sets the config parameters.
     */
    protected $config;

    /**
     * ItemCollection constructor.
     *
     * @param  array|mixed  $items
     */
    public function __construct($items, $config = [])
    {
        parent::__construct($items);

        $this->config = $config;
    }

    /**
     * get the sum of price
     *
     * @return mixed|null
     */
    public function getPriceSum()
    {
        return Helpers::formatValue($this->price * $this->quantity, $this->config['format_numbers'], $this->config);
    }

    public function __get($name)
    {
        if ($this->has($name) || $name == 'model') {
            return ! is_null($this->get($name)) ? $this->get($name) : $this->getAssociatedModel();
        }

        return null;
    }

    /**
     * return the associated model of an item
     *
     * @return mixed
     */
    protected function getAssociatedModel(): mixed
    {
        if (! $this->has('associatedModel')) {
            return null;
        }

        $associatedModel = $this->get('associatedModel');

        return with(new $associatedModel)->find($this->get('id'));
    }

    /**
     * check if item has conditions
     *
     * @return bool
     */
    public function hasConditions()
    {
        if (! isset($this['conditions'])) {
            return false;
        }
        if (is_array($this['conditions'])) {
            return count($this['conditions']) > 0;
        }
        $conditionInstance = 'Wearepixel\\Cart\\CartCondition';
        if ($this['conditions'] instanceof $conditionInstance) {
            return true;
        }

        return false;
    }

    /**
     * check if item has conditions
     *
     * @return mixed|null
     */
    public function getConditions()
    {
        if (! $this->hasConditions()) {
            return [];
        }

        $conditionsArray = [];

        $conditions = $this['conditions'];

        // check if we're already an array of CartConditions
        foreach ($conditions as $key => $condition) {
            if (is_object($condition) && $condition instanceof CartCondition) {
                return $conditions;
            }
        }

        $hasSubArray = false;

        foreach ($conditions as $key => $condition) {
            if (is_array($condition)) {
                $hasSubArray = true;
                $conditionsArray[] = new CartCondition($condition);
            } elseif ($condition instanceof CartCondition) {
                $conditionsArray[] = $condition;
            }
        }

        if (! $hasSubArray) {
            if ($conditions instanceof CartCondition) {
                return $conditions;
            }

            $conditionsArray[] = new CartCondition($conditions);
        }

        return $conditionsArray;
    }

    /**
     * get the single price in which conditions are already applied
     *
     * @param  bool  $formatted
     * @return mixed|null
     */
    public function getPriceWithConditions($formatted = true)
    {
        $originalPrice = $this->price;

        $newPrice = 0.00;
        $processed = 0;

        if ($this->hasConditions()) {
            if (is_array($this->getConditions())) {
                foreach ($this->getConditions() as $condition) {
                    ($processed > 0) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                    $newPrice = $condition->applyCondition($toBeCalculated);
                    $processed++;
                }
            } else {
                $newPrice = $this->getConditions()->applyCondition($originalPrice);
            }

            return Helpers::formatValue($newPrice, $formatted, $this->config);
        }

        return Helpers::formatValue($originalPrice, $formatted, $this->config);
    }

    /**
     * get the sum of price in which conditions are already applied
     *
     * @param  bool  $formatted
     * @return mixed|null
     */
    public function getPriceSumWithConditions($formatted = true)
    {
        $conditions = $this->hasConditions() ? $this->getConditions() : [];
        $conditions = is_array($conditions) ? $conditions : [$conditions];

        $hasLimit = collect($conditions)->some(fn ($c) => $c->getAppliesToQuantity() !== null);

        if (! $hasLimit) {
            return Helpers::formatValue($this->getPriceWithConditions(false) * $this->quantity, $formatted, $this->config);
        }

        $total = 0.0;

        for ($i = 1; $i <= $this->quantity; $i++) {
            $price = $this->price;

            foreach ($conditions as $condition) {
                $limit = $condition->getAppliesToQuantity();

                if ($limit === null || $i <= $limit) {
                    $price = $condition->applyCondition($price);
                }
            }

            $total += $price;
        }

        return Helpers::formatValue($total, $formatted, $this->config);
    }
}
