<?php

namespace Wearepixel\Cart;

use Wearepixel\Cart\Helpers\Helpers;
use Wearepixel\Cart\Validators\CartItemValidator;
use Wearepixel\Cart\Exceptions\InvalidItemException;
use Wearepixel\Cart\Exceptions\UnknownModelException;

class Cart
{
    protected $session;
    protected $events;

    protected $instanceName;

    protected $sessionKey;
    protected $sessionKeyCartItems;
    protected $sessionKeyCartConditions;

    protected $config;

    protected $currentItemId;

    protected $decimals;
    protected $decPoint;

    public function __construct($session, $events, $instanceName, $session_key, $config)
    {
        $this->events = $events;
        $this->session = $session;
        $this->instanceName = $instanceName;
        $this->sessionKey = $session_key;
        $this->sessionKeyCartItems = $this->sessionKey . '_cart_items';
        $this->sessionKeyCartConditions = $this->sessionKey . '_cart_conditions';
        $this->config = $config;
        $this->currentItemId = null;

        $this->fireEvent('created');
    }

    /**
     * sets the session key
     *
     * @param  string  $sessionKey  the session key or identifier
     * @return $this|bool
     *
     * @throws \Exception
     */
    public function session($sessionKey)
    {
        if (! $sessionKey) {
            throw new \Exception('Session key is required.');
        }

        $this->sessionKey = $sessionKey;
        $this->sessionKeyCartItems = $this->sessionKey . '_cart_items';
        $this->sessionKeyCartConditions = $this->sessionKey . '_cart_conditions';

        return $this;
    }

    /**
     * get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }

    /**
     * get an item on a cart by item ID
     */
    public function get($itemId): ?ItemCollection
    {
        return $this->getContent()->get($itemId);
    }

    /**
     * check if we have this item in the cart by item ID
     */
    public function has($itemId): bool
    {
        return $this->getContent()->has($itemId);
    }

    /**
     * add item to the cart, it can be an array or multi dimensional array
     *
     * @param  string|array  $id
     * @return $this
     *
     * @throws InvalidItemException
     */
    public function add(
        string|int|array $id,
        ?string $name = null,
        ?float $price = null,
        int|float|null $quantity = null,
        array $attributes = [],
        array|CartCondition $conditions = [],
        ?string $associatedModel = null
    ): self {
        // check if we are adding an array of items
        if (is_array($id)) {
            // check if we are adding a multi dimensional array
            if (Helpers::isMultiArray($id)) {
                foreach ($id as $item) {
                    $this->add(
                        $item['id'],
                        $item['name'],
                        isset($item['price']) ? $item['price'] : null,
                        $item['quantity'],
                        Helpers::issetAndHasValueOrAssignDefault($item['attributes'], []),
                        Helpers::issetAndHasValueOrAssignDefault($item['conditions'], []),
                        Helpers::issetAndHasValueOrAssignDefault($item['associatedModel'], null)
                    );
                }
            } else {
                $this->add(
                    $id['id'],
                    $id['name'],
                    isset($id['price']) ? $id['price'] : null,
                    $id['quantity'],
                    Helpers::issetAndHasValueOrAssignDefault($id['attributes'], []),
                    Helpers::issetAndHasValueOrAssignDefault($id['conditions'], []),
                    Helpers::issetAndHasValueOrAssignDefault($id['associatedModel'], null)
                );
            }

            return $this;
        }

        // if we are here, we are adding a single item
        $data = [
            'id' => $id,
            'name' => $name,
            'price' => Helpers::normalizePrice($price),
            'quantity' => $quantity,
            'attributes' => new ItemAttributeCollection($attributes),
            'conditions' => $conditions,
        ];

        if (isset($associatedModel) && $associatedModel != '') {
            $data['associatedModel'] = $associatedModel;
        }

        // validate data
        $item = $this->validate($data);

        // if the item is already in the cart we will just update it
        if ($this->get($id)) {
            $this->update($id, $item);
        } else {
            $this->addItem($id, $item);
        }

        $this->currentItemId = $id;

        return $this;
    }

    /**
     * Update a cart item with whatever data is passed in
     */
    public function update(int|string $id, array $data): bool
    {
        if ($this->fireEvent('updating', $data) === false) {
            return false;
        }

        $cart = $this->getContent();

        $item = $cart->get($id);

        foreach ($data as $key => $value) {
            if ($key == 'quantity') {
                if (is_array($value)) {
                    if (isset($value['relative'])) {
                        if (isset($value['relative']) && $value['relative'] === true) {
                            $item = $this->updateQuantityRelative($item, $key, $value['value']);
                        } else {
                            $item = $this->updateQuantityNotRelative($item, $key, $value['value']);
                        }
                    }
                } else {
                    $item = $this->updateQuantityRelative($item, $key, $value);
                }
            } elseif ($key == 'attributes') {
                $item[$key] = new ItemAttributeCollection($value);
            } else {
                $item[$key] = $value;
            }
        }

        // update the item in the cart
        $cart->put($id, $item);

        $this->save($cart);

        $this->fireEvent('updated', $item);

        return true;
    }

    /**
     * Set the config
     *
     * @param  array  $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * add condition on an existing item on the cart
     *
     * @param  int|string  $productId
     * @param  CartCondition  $itemCondition
     * @return $this
     */
    public function addItemCondition($productId, $itemCondition)
    {
        if ($product = $this->get($productId)) {
            $conditionInstance = '\\Wearepixel\\Cart\\CartCondition';

            if ($itemCondition instanceof $conditionInstance) {
                // we need to copy first to a temporary variable to hold the conditions
                // to avoid hitting this error "Indirect modification of overloaded element of Wearepixel\Cart\ItemCollection has no effect"
                // this is due to laravel Collection instance that implements Array Access
                // // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
                $itemConditionTempHolder = $product['conditions'];

                if (is_array($itemConditionTempHolder)) {
                    array_push($itemConditionTempHolder, $itemCondition);
                } else {
                    $itemConditionTempHolder = $itemCondition;
                }

                $this->update($productId, [
                    'conditions' => $itemConditionTempHolder, // the newly updated conditions
                ]);
            }
        }

        return $this;
    }

    /**
     * removes an item on cart by item ID
     *
     * @return bool
     */
    public function remove($id)
    {
        $cart = $this->getContent();

        if ($this->fireEvent('removing', $id) === false) {
            return false;
        }

        $cart->forget($id);

        $this->save($cart);

        $this->fireEvent('removed', $id);

        return true;
    }

    /**
     * clear cart
     *
     * @return bool
     */
    public function clear()
    {
        if ($this->fireEvent('clearing') === false) {
            return false;
        }

        $this->session->put(
            $this->sessionKeyCartItems,
            []
        );

        $this->fireEvent('cleared');

        return true;
    }

    /**
     * Adds a condition to the cart
     */
    public function condition(array|CartCondition $condition): self
    {
        if (is_array($condition)) {
            foreach ($condition as $c) {
                $this->condition($c);
            }

            return $this;
        }

        return $this->condition($condition);
    }

    /**
     * get conditions applied on the cart
     *
     * @param  bool  $active  - If true, only conditions actually applied on the cart are returned
     */
    public function getConditions(bool $active = false): CartConditionCollection
    {
        $conditions = new CartConditionCollection(
            $this->session->get($this->sessionKeyCartConditions)
        );

        if ($active) {
            return $conditions->filter(function (CartCondition $condition) {
                $amount = $condition->getTarget() == 'subtotal' ? $this->getSubTotal(false) : $this->getTotal();

                return $condition->getMinimum() === null || $condition->getMinimum() <= $amount;
            })->filter(function (CartCondition $condition) {
                $amount = $condition->getTarget() == 'subtotal' ? $this->getSubTotal(false) : $this->getTotal();

                return $condition->getMaximum() === null || $condition->getMaximum() >= $amount;
            });
        }

        return $conditions;
    }

    /**
     * get condition applied on the cart by its name
     *
     * @return CartCondition
     */
    public function getCondition($conditionName)
    {
        return $this->getConditions()->get($conditionName);
    }

    /**
     * Get all the condition filtered by Type
     * Please Note that this will only return condition added on cart bases, not those conditions added
     * specifically on an per item bases
     *
     * @return CartConditionCollection
     */
    public function getConditionsByType($type)
    {
        return $this->getConditions()->filter(function (CartCondition $condition) use ($type) {
            return $condition->getType() == $type;
        });
    }

    /**
     * Remove all the condition with the $type specified
     */
    public function removeConditionsByType(string $type)
    {
        $this->getConditionsByType($type)->each(function ($condition) {
            $this->removeCartCondition($condition->getName());
        });
    }

    /**
     * Remove a condition from the cart
     */
    public function removeCartCondition($conditionName)
    {
        $conditions = $this->getConditions();

        $conditions->pull($conditionName);

        $this->saveConditions($conditions);
    }

    /**
     * remove a condition that has been applied on an item that is already on the cart
     *
     * @return bool
     */
    public function removeItemCondition($itemId, $conditionName)
    {
        if (! $item = $this->getContent()->get($itemId)) {
            return false;
        }

        if ($this->itemHasConditions($item)) {
            // NOTE:
            // we do it this way, we get first conditions and store
            // it in a temp variable $originalConditions, then we will modify the array there
            // and after modification we will store it again on $item['conditions']
            // This is because of ArrayAccess implementation
            // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect

            $tempConditionsHolder = $item['conditions'];

            // if the item's conditions is in array format
            // we will iterate through all of it and check if the name matches
            // to the given name the user wants to remove, if so, remove it
            if (is_array($tempConditionsHolder)) {
                foreach ($tempConditionsHolder as $k => $condition) {
                    if ($condition->getName() == $conditionName) {
                        unset($tempConditionsHolder[$k]);
                    }
                }

                $item['conditions'] = $tempConditionsHolder;
            }

            // if the item condition is not an array, we will check if it is
            // an instance of a Condition, if so, we will check if the name matches
            // on the given condition name the user wants to remove, if so,
            // lets just make $item['conditions'] an empty array as there's just 1 condition on it anyway
            else {
                $conditionInstance = 'Wearepixel\\Cart\\CartCondition';

                if ($item['conditions'] instanceof $conditionInstance) {
                    if ($tempConditionsHolder->getName() == $conditionName) {
                        $item['conditions'] = [];
                    }
                }
            }
        }

        $this->update($itemId, [
            'conditions' => $item['conditions'],
        ]);

        return true;
    }

    /**
     * remove all conditions that has been applied on an item that is already on the cart
     *
     * @return bool
     */
    public function clearItemConditions($itemId)
    {
        if (! $item = $this->getContent()->get($itemId)) {
            return false;
        }

        $this->update($itemId, [
            'conditions' => [],
        ]);

        return true;
    }

    /**
     * Clear cart conditions (this does not clear item conditions)
     */
    public function clearCartConditions(): void
    {
        $this->session->put(
            $this->sessionKeyCartConditions,
            []
        );
    }

    /**
     * Clear all conditions from all items and the cart
     */
    public function clearAllConditions(): void
    {
        $cart = $this->getContent();

        $cart->each(function ($item) {
            $this->clearItemConditions($item->id);
        });

        $this->clearCartConditions();
    }

    /**
     * get cart sub total without conditions
     *
     * @param  bool  $formatted
     * @return float
     */
    public function getSubTotalWithoutConditions($formatted = true)
    {
        $cart = $this->getContent();

        $sum = $cart->sum(function ($item) {
            return $item->getPriceSum();
        });

        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }

    /**
     * get cart sub total
     *
     * @param  bool  $formatted
     */
    public function getSubTotal($formatted = true): int|float|string
    {
        $subTotal = 0.00;
        // add all the items together with conditions applied
        $subTotalSum = $this->getContent()->sum(function (ItemCollection $item) {
            return $item->getPriceSumWithConditions(false);
        });

        // get the conditions that are meant to be applied
        // on the subtotal and apply it here before returning the subtotal
        $conditionsForSubtotal = $this->getConditions()
            ->filter(function (CartCondition $condition) {
                return $condition->getTarget() === 'subtotal';
            })
            ->filter(function (CartCondition $condition) use ($subTotalSum) {
                return $condition->getMinimum() === null || $condition->getMinimum() <= $subTotalSum;
            })
            ->filter(function (CartCondition $condition) use ($subTotalSum) {
                return $condition->getMaximum() === null || $condition->getMaximum() >= $subTotalSum;
            })
            ->sortBy(function (CartCondition $condition) {
                return $condition->getOrder();
            });

        if ($conditionsForSubtotal->isEmpty()) {
            return Helpers::formatValue($subTotalSum, $formatted, $this->config);
        }

        $index = 0;

        $conditionsForSubtotal->each(function (CartCondition $condition) use ($subTotalSum, &$subTotal, &$index) {
            $toBeCalculated = $index > 0 ? $subTotal : $subTotalSum;

            $subTotal = $condition->applyCondition($toBeCalculated);

            $index++;
        });

        return Helpers::formatValue($subTotal, $formatted, $this->config);
    }

    /**
     * Get the total of the cart with conditions applied
     */
    public function getTotal(): int|float
    {
        $total = 0.00;
        $subTotal = $this->getSubTotal(false);

        $conditionsForTotal = $this->getConditions()
            ->filter(function (CartCondition $condition) {
                return $condition->getTarget() === 'total';
            })
            ->filter(function (CartCondition $condition) use ($subTotal) {
                return $condition->getMinimum() === null || $condition->getMinimum() <= $subTotal;
            })
            ->filter(function (CartCondition $condition) use ($subTotal) {
                return $condition->getMaximum() === null || $condition->getMaximum() >= $subTotal;
            })
            ->sortBy(function (CartCondition $condition) {
                return $condition->getOrder();
            });

        // if no conditions were added, just return the sub total
        if (! $conditionsForTotal->count()) {
            return Helpers::formatValue($subTotal, $this->config['format_numbers'], $this->config);
        }

        $conditionsForTotal->each(function (CartCondition $condition) use ($subTotal, &$total) {
            $toBeCalculated = $total > 0 ? $total : $subTotal;

            $total = $condition->applyCondition($toBeCalculated);
        });

        return Helpers::formatValue($total, $this->config['format_numbers'], $this->config);
    }

    /**
     * Get the calculated value for a condition by it's name
     */
    public function getCalculatedValueForCondition(string $conditionName): int|float
    {
        $conditions = $this->getConditions();

        $subTotal = $this->getSubTotalWithoutConditions(false);

        foreach ($conditions as $condition) {
            $conditionValue = $condition->getCalculatedValue($subTotal);

            if ($condition->getName() === $conditionName) {
                return $conditionValue;
            }

            $subTotal -= $conditionValue;
        }

        return 0;
    }

    /**
     * get total quantity of items in the cart
     *
     * @return int
     */
    public function getTotalQuantity()
    {
        $items = $this->getContent();

        if ($items->isEmpty()) {
            return 0;
        }

        $count = $items->sum(function ($item) {
            return $item['quantity'];
        });

        return $count;
    }

    /**
     * get the cart
     *
     * @return CartCollection
     */
    public function getContent()
    {
        return (new CartCollection($this->session->get($this->sessionKeyCartItems)))->reject(function ($item) {
            return ! ($item instanceof ItemCollection);
        });
    }

    /**
     * check if cart is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getContent()->isEmpty();
    }

    /**
     * validate Item data
     *
     * @return array $item;
     *
     * @throws InvalidItemException
     */
    protected function validate($item)
    {
        $rules = [
            'id' => 'required',
            'name' => 'required',
            'quantity' => 'required|numeric|min:0.1',
        ];

        $validator = CartItemValidator::make($item, $rules);

        if ($validator->fails()) {
            if ($validator->errors()->first('id') === 'validation.required') {
                throw new InvalidItemException('The id field is required.');
            }

            if ($validator->errors()->first('name') === 'validation.required') {
                throw new InvalidItemException('The name field is required.');
            }

            if ($validator->errors()->first('quantity') === 'validation.min.numeric') {
                throw new InvalidItemException('The quantity must be at least 0.1.');
            }
        }

        return $item;
    }

    /**
     * add row to cart collection
     *
     * @return bool
     */
    protected function addItem($id, $item)
    {
        if ($this->fireEvent('adding', $item) === false) {
            return false;
        }

        $cart = $this->getContent();

        $cart->put($id, new ItemCollection($item, $this->config));

        $this->save($cart);

        $this->fireEvent('added', $item);

        return true;
    }

    /**
     * save the cart
     *
     * @param  $cart  CartCollection
     */
    protected function save($cart)
    {
        $this->session->put($this->sessionKeyCartItems, $cart);
    }

    /**
     * save the cart conditions
     */
    protected function saveConditions($conditions)
    {
        $this->session->put($this->sessionKeyCartConditions, $conditions);
    }

    /**
     * check if an item has condition
     *
     * @return bool
     */
    protected function itemHasConditions($item)
    {
        if (! isset($item['conditions'])) {
            return false;
        }

        if (is_array($item['conditions'])) {
            return count($item['conditions']) > 0;
        }

        $conditionInstance = 'Wearepixel\\Cart\\CartCondition';

        if ($item['conditions'] instanceof $conditionInstance) {
            return true;
        }

        return false;
    }

    /**
     * update a cart item quantity relative to its current quantity
     *
     * @return mixed
     */
    protected function updateQuantityRelative($item, $key, $value)
    {
        if (preg_match('/\-/', $value) == 1) {
            $value = (float) str_replace('-', '', $value);

            // we will not allowed to reduced quantity to 0, so if the given value
            // would result to item quantity of 0, we will not do it.
            if (($item[$key] - $value) > 0) {
                $item[$key] -= $value;
            }
        } elseif (preg_match('/\+/', $value) == 1) {
            $item[$key] += (float) str_replace('+', '', $value);
        } else {
            $item[$key] += (float) $value;
        }

        return $item;
    }

    /**
     * update cart item quantity not relative to its current quantity value
     *
     * @return mixed
     */
    protected function updateQuantityNotRelative($item, $key, $value)
    {
        $item[$key] = (float) $value;

        return $item;
    }

    /**
     * Setter for decimals. Change value on demand.
     */
    public function setDecimals($decimals)
    {
        $this->decimals = $decimals;
    }

    /**
     * @return mixed
     */
    protected function fireEvent($name, $value = [])
    {
        return $this->events->dispatch($this->getInstanceName() . '.' . $name, array_values([$value, $this]), true);
    }

    /**
     * Associate the cart item with the given id with the given model.
     *
     * @param  string  $id
     * @param  mixed  $model
     * @return void
     */
    public function associate($model)
    {
        if (is_string($model) && ! class_exists($model)) {
            throw new UnknownModelException("The supplied model {$model} does not exist.");
        }

        $cart = $this->getContent();

        $item = $cart->pull($this->currentItemId);

        $item['associatedModel'] = $model;

        $cart->put($this->currentItemId, new ItemCollection($item, $this->config));

        $this->save($cart);

        return $this;
    }
}
