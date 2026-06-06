<?php

declare(strict_types=1);

namespace Wearepixel\Cart;

use Closure;
use PHPUnit\Framework\Assert;
use Wearepixel\Cart\Coupons\Coupon;
use Wearepixel\Cart\Drivers\NullDriver;
use Wearepixel\Cart\Helpers\Helpers;
use Wearepixel\Cart\Shipping\ShippingRate;
use Wearepixel\Cart\Tax\TaxRule;
use Wearepixel\Cart\Testing\CartFactory;
use Wearepixel\Cart\Validators\CartItemValidator;
use Wearepixel\Cart\Exceptions\InvalidItemException;
use Wearepixel\Cart\Exceptions\UnknownModelException;
use Wearepixel\Cart\Exceptions\InvalidConditionException;
use Illuminate\Contracts\Events\Dispatcher;
use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class Cart
{
    protected CartDriver $driver;

    protected Dispatcher $events;

    protected string $instanceName;

    protected array $config;

    protected mixed $currentItemId;

    public function __construct(CartDriver $driver, Dispatcher $events, string $instanceName, array $config)
    {
        $this->driver = $driver;
        $this->events = $events;
        $this->instanceName = $instanceName;
        $this->config = $config;
        $this->currentItemId = null;

        $this->fireEvent('Created');
    }

    public static function extend(string $name, Closure $resolver): void
    {
        app('cart.manager')->extend($name, fn($app) => $resolver($app['session']->getId(), config('cart')));
    }

    public function getDriver(): CartDriver
    {
        return $this->driver;
    }

    /**
     * @internal For testing purposes only. Not intended for production use.
     */
    public function fake(): CartFactory
    {
        $this->driver = new NullDriver($this->driver->getSessionKey());

        return new CartFactory($this);
    }

    public function assertContains(int|string $id): void
    {
        Assert::assertTrue(
            $this->has($id),
            "Failed asserting that cart contains item [{$id}]."
        );
    }

    public function assertCount(int $count): void
    {
        Assert::assertCount(
            $count,
            $this->getContent(),
            "Failed asserting that cart has [{$count}] items."
        );
    }

    public function assertTotalQuantity(int|float $quantity): void
    {
        Assert::assertEqualsWithDelta(
            $quantity,
            (float) $this->getTotalQuantity(),
            0.0001,
            "Failed asserting that cart total quantity is [{$quantity}]."
        );
    }

    public function assertSubTotal(float $amount): void
    {
        Assert::assertEqualsWithDelta(
            $amount,
            (float) $this->getSubTotal(false),
            0.0001,
            "Failed asserting that cart subtotal is [{$amount}]."
        );
    }

    public function assertTotal(float $amount): void
    {
        Assert::assertEqualsWithDelta(
            $amount,
            (float) $this->getTotal(),
            0.0001,
            "Failed asserting that cart total is [{$amount}]."
        );
    }

    public function assertConditionApplied(string $conditionName): void
    {
        Assert::assertNotNull(
            $this->getCondition($conditionName),
            "Failed asserting that condition [{$conditionName}] is applied to the cart."
        );
    }

    public function assertEmpty(): void
    {
        Assert::assertTrue(
            $this->isEmpty(),
            'Failed asserting that the cart is empty.'
        );
    }

    public function assertNotEmpty(): void
    {
        Assert::assertFalse(
            $this->isEmpty(),
            'Failed asserting that the cart is not empty.'
        );
    }

    public function getSessionKey(): string
    {
        return $this->driver->getSessionKey();
    }

    public function setSessionKey(string $sessionKey): void
    {
        $this->driver->setSessionKey($sessionKey);
    }

    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    public function get($itemId): ?ItemCollection
    {
        return $this->getContent()->get($itemId);
    }

    public function has($itemId): bool
    {
        return $this->getContent()->has($itemId);
    }

    public function add(
        string|int|array $id,
        ?string $name = null,
        ?float $price = null,
        int|float|null $quantity = null,
        array $attributes = [],
        array|CartCondition $conditions = [],
        ?string $associatedModel = null
    ): self {
        if (is_array($id)) {
            if (Helpers::isMultiArray($id)) {
                foreach ($id as $item) {
                    $this->add(
                        $item['id'],
                        $item['name'],
                        $item['price'] ?? null,
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
                    $id['price'] ?? null,
                    $id['quantity'],
                    Helpers::issetAndHasValueOrAssignDefault($id['attributes'], []),
                    Helpers::issetAndHasValueOrAssignDefault($id['conditions'], []),
                    Helpers::issetAndHasValueOrAssignDefault($id['associatedModel'], null)
                );
            }

            return $this;
        }

        $data = [
            'id'         => $id,
            'name'       => $name,
            'price'      => Helpers::normalizePrice($price),
            'quantity'   => $quantity,
            'attributes' => new ItemAttributeCollection($attributes),
            'conditions' => $conditions,
        ];

        if (isset($associatedModel) && $associatedModel !== '') {
            $data['associatedModel'] = $associatedModel;
        }

        $item = $this->validate($data);

        if ($this->get($id)) {
            $this->update($id, $item);
        } else {
            $this->addItem($id, $item);
        }

        $this->currentItemId = $id;
        $this->removeInvalidItems();

        return $this;
    }

    public function removeInvalidItems(): bool
    {
        foreach ($this->getContent() as $index => $cart) {
            if (! isset($cart['id'], $cart['name'], $cart['quantity'])) {
                $this->remove($index);
            }
        }

        return true;
    }

    public function update(int|string $id, array $data): bool
    {
        if ($this->fireEvent('Updating', 'item', $data) === false) {
            return false;
        }

        $cart = $this->getContent();
        $item = $cart->get($id);

        foreach ($data as $key => $value) {
            if ($key === 'quantity') {
                if (is_array($value)) {
                    if (isset($value['relative']) && $value['relative'] === true) {
                        $item = $this->updateQuantityRelative($item, $key, $value['value']);
                    } else {
                        $item = $this->updateQuantityNotRelative($item, $key, $value['value']);
                    }
                } else {
                    $item = $this->updateQuantityRelative($item, $key, $value);
                }
            } elseif ($key === 'attributes') {
                $item[$key] = new ItemAttributeCollection($value);
            } else {
                $item[$key] = $value;
            }
        }

        $cart->put($id, $item);
        $this->save($cart);
        $this->fireEvent('Updated', 'item', $item);

        return true;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function addItemCondition($productId, $itemCondition): self
    {
        if ($product = $this->get($productId)) {
            $conditionInstance = CartCondition::class;

            if ($itemCondition instanceof $conditionInstance) {
                $itemConditionTempHolder = $product['conditions'];

                if (is_array($itemConditionTempHolder)) {
                    array_push($itemConditionTempHolder, $itemCondition);
                } else {
                    $itemConditionTempHolder = $itemCondition;
                }

                $this->update($productId, ['conditions' => $itemConditionTempHolder]);
            }
        }

        return $this;
    }

    public function remove($id): bool
    {
        $cart = $this->getContent();

        if ($this->fireEvent('Removing', 'item', $id) === false) {
            return false;
        }

        $cart->forget($id);
        $this->save($cart);
        $this->fireEvent('Removed', 'item', $id);

        return true;
    }

    public function clear(): bool
    {
        if ($this->fireEvent('Clearing') === false) {
            return false;
        }

        $this->driver->flush();
        $this->fireEvent('Cleared');

        return true;
    }

    public function clearItems(): bool
    {
        if ($this->fireEvent('Clearing') === false) {
            return false;
        }

        $this->driver->clearItems();
        $this->fireEvent('Cleared');

        return true;
    }

    public function condition(array|CartCondition $condition): self
    {
        if (is_array($condition)) {
            foreach ($condition as $c) {
                $this->condition($c);
            }

            return $this;
        }

        $conditions = $this->getConditions();

        if ($condition->getOrder() === 0) {
            $last = $conditions->last();
            $condition->setOrder(! is_null($last) ? $last->getOrder() + 1 : 1);
        }

        $conditions->put($condition->getName(), $condition);

        $conditions = $conditions->reject(fn($c) => empty($c))
            ->sortBy(fn($c) => $c->getOrder());

        $this->saveConditions($conditions);

        return $this;
    }

    public function coupon(Coupon $coupon): self
    {
        return $this->condition($coupon->toCondition());
    }

    public function tax(TaxRule $taxRule): self
    {
        return $this->condition($taxRule->toCondition());
    }

    public function shipping(ShippingRate $shippingRate): self
    {
        return $this->condition($shippingRate->toCondition());
    }

    public function getConditions(bool $array = false, bool $active = false): CartConditionCollection|array
    {
        $conditions = new CartConditionCollection($this->driver->getConditions());

        $conditions = $conditions->reject(fn($c) => empty($c))
            ->transform(fn($c) => $c instanceof CartCondition ? $c : new CartCondition($c));

        if ($active) {
            return $conditions
                ->filter(function (CartCondition $condition) {
                    $amount = $condition->getTarget() === 'subtotal' ? $this->getSubTotal(false) : $this->getTotal();

                    return $condition->getMinimum() === null || $condition->getMinimum() <= $amount;
                })
                ->filter(function (CartCondition $condition) {
                    $amount = $condition->getTarget() === 'subtotal' ? $this->getSubTotal(false) : $this->getTotal();

                    return $condition->getMaximum() === null || $condition->getMaximum() >= $amount;
                });
        }

        return $array
            ? $conditions->map(fn(CartCondition $c) => $c->toArray())->toArray()
            : $conditions;
    }

    public function getCondition($conditionName): ?CartCondition
    {
        return $this->getConditions()->get($conditionName);
    }

    public function getConditionsByType(string $type): CartConditionCollection
    {
        return $this->getConditions()->filter(fn(CartCondition $c) => $c->getType() === $type);
    }

    public function removeConditionsByType(string $type): void
    {
        $this->getConditionsByType($type)->each(fn($c) => $this->removeCartCondition($c->getName()));
    }

    public function removeCartCondition($conditionName): void
    {
        $conditions = $this->getConditions();
        $conditions->pull($conditionName);
        $this->saveConditions($conditions);
    }

    public function removeItemCondition($itemId, $conditionName): bool
    {
        if (! $item = $this->getContent()->get($itemId)) {
            return false;
        }

        if ($this->itemHasConditions($item)) {
            $tempConditionsHolder = $item['conditions'];

            if (is_array($tempConditionsHolder)) {
                foreach ($tempConditionsHolder as $k => $condition) {
                    if ($condition->getName() === $conditionName) {
                        unset($tempConditionsHolder[$k]);
                    }
                }
                $item['conditions'] = $tempConditionsHolder;
            } else {
                if ($item['conditions'] instanceof CartCondition) {
                    if ($tempConditionsHolder->getName() === $conditionName) {
                        $item['conditions'] = [];
                    }
                }
            }
        }

        $this->update($itemId, ['conditions' => $item['conditions']]);

        return true;
    }

    public function clearItemConditions($itemId): bool
    {
        if (! $this->getContent()->get($itemId)) {
            return false;
        }

        $this->update($itemId, ['conditions' => []]);

        return true;
    }

    public function clearCartConditions(): void
    {
        $this->driver->putConditions([]);
    }

    public function clearAllConditions(): void
    {
        $this->getContent()->each(fn($item) => $this->clearItemConditions($item->id));
        $this->clearCartConditions();
    }

    public function getSubTotalWithoutConditions(bool $formatted = true): int|float|string
    {
        $sum = $this->getContent()->sum(fn($item) => $item->getPriceSum());

        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }

    public function getSubTotal(bool $formatted = true): int|float|string
    {
        $subTotal = 0.00;

        $subTotalSum = $this->getContent()->sum(fn(ItemCollection $item) => $item->getPriceSumWithConditions(false));

        $conditionsForSubtotal = $this->getConditions()
            ->filter(fn(CartCondition $c) => $c->getTarget() === 'subtotal')
            ->filter(fn(CartCondition $c) => $c->getMinimum() === null || $c->getMinimum() <= $subTotalSum)
            ->filter(fn(CartCondition $c) => $c->getMaximum() === null || $c->getMaximum() >= $subTotalSum)
            ->sortBy(fn(CartCondition $c) => $c->getOrder());

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

    public function getTotal(): int|float
    {
        $total = 0.00;
        $subTotal = $this->getSubTotal(false);

        $conditionsForTotal = $this->getConditions()
            ->filter(fn(CartCondition $c) => $c->getTarget() === 'total')
            ->filter(fn(CartCondition $c) => $c->getMinimum() === null || $c->getMinimum() <= $subTotal)
            ->filter(fn(CartCondition $c) => $c->getMaximum() === null || $c->getMaximum() >= $subTotal)
            ->sortBy(fn(CartCondition $c) => $c->getOrder());

        if (! $conditionsForTotal->count()) {
            return Helpers::formatValue($subTotal, $this->config['format_numbers'], $this->config);
        }

        $index = 0;
        $conditionsForTotal->each(function (CartCondition $condition) use ($subTotal, &$total, &$index) {
            $toBeCalculated = $index > 0 ? $total : $subTotal;
            $total = $condition->applyCondition($toBeCalculated);
            $index++;
        });

        return Helpers::formatValue($total, $this->config['format_numbers'], $this->config);
    }

    public function getCalculatedValueForCondition(string $conditionName): int|float
    {
        $subTotal = $this->getSubTotalWithoutConditions(false);

        foreach ($this->getConditions() as $condition) {
            $conditionValue = $condition->getCalculatedValue($subTotal);

            if ($condition->getName() === $conditionName) {
                return $conditionValue;
            }

            $subTotal -= $conditionValue;
        }

        return 0;
    }

    public function getTotalQuantity(): int|float
    {
        $items = $this->getContent();

        if ($items->isEmpty()) {
            return 0;
        }

        return $items->sum(fn($item) => $item['quantity']);
    }

    public function getContent(): CartCollection
    {
        return (new CartCollection($this->driver->getItems()))
            ->transform(function ($item) {
                if ($item instanceof ItemCollection) {
                    return $item;
                }

                if (is_array($item) && isset($item['attributes']) && is_array($item['attributes'])) {
                    $item['attributes'] = new ItemAttributeCollection($item['attributes']);
                }

                if (is_array($item) && isset($item['conditions']) && is_array($item['conditions'])) {
                    $item['conditions'] = array_map(
                        fn($c) => is_array($c) ? new CartCondition($c) : $c,
                        $item['conditions']
                    );
                }

                return new ItemCollection($item, $this->config);
            })
            ->reject(fn($item) => ! ($item instanceof ItemCollection));
    }

    public function isEmpty(): bool
    {
        return $this->getContent()->isEmpty();
    }

    public function associate(mixed $model): self
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

    protected function validate(array $item): array
    {
        $rules = [
            'id'       => 'required',
            'name'     => 'required',
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

    protected function addItem(int|string $id, array $item): bool
    {
        if ($this->fireEvent('Adding', 'item', $item) === false) {
            return false;
        }

        $cart = $this->getContent();
        $cart->put($id, new ItemCollection($item, $this->config));
        $this->save($cart);
        $this->fireEvent('Added', 'item', $item);

        return true;
    }

    protected function save(CartCollection $cart): void
    {
        $this->driver->putItems($this->serializeItems($cart));
    }

    protected function saveConditions(CartConditionCollection $conditions): void
    {
        $this->driver->putConditions($this->serializeConditions($conditions));
    }

    private function serializeItems(CartCollection $cart): array
    {
        return $cart->map(function (ItemCollection $item) {
            $data = $item->toArray();

            $conditions = $item['conditions'] ?? [];

            if ($conditions instanceof CartCondition) {
                $data['conditions'] = [$conditions->toArray()];
            } elseif (is_array($conditions) || $conditions instanceof \Traversable) {
                $data['conditions'] = collect($conditions)
                    ->map(fn($c) => $c instanceof CartCondition ? $c->toArray() : $c)
                    ->toArray();
            } else {
                $data['conditions'] = [];
            }

            if ($item['attributes'] instanceof ItemAttributeCollection) {
                $data['attributes'] = $item['attributes']->toArray();
            }

            return $data;
        })->toArray();
    }

    private function serializeConditions(CartConditionCollection $conditions): array
    {
        return $conditions->map(fn(CartCondition $c) => $c->toArray())->all();
    }

    protected function itemHasConditions(ItemCollection $item): bool
    {
        if (! isset($item['conditions'])) {
            return false;
        }

        if (is_array($item['conditions'])) {
            return count($item['conditions']) > 0;
        }

        return $item['conditions'] instanceof CartCondition;
    }

    protected function updateQuantityRelative(ItemCollection $item, string $key, mixed $value): ItemCollection
    {
        if (preg_match('/\-/', (string) $value) === 1) {
            $value = (float) str_replace('-', '', (string) $value);
            if (($item[$key] - $value) > 0) {
                $item[$key] -= $value;
            }
        } elseif (preg_match('/\+/', (string) $value) === 1) {
            $item[$key] += (float) str_replace('+', '', (string) $value);
        } else {
            $item[$key] += (float) $value;
        }

        return $item;
    }

    protected function updateQuantityNotRelative(ItemCollection $item, string $key, mixed $value): ItemCollection
    {
        $item[$key] = (float) $value;

        return $item;
    }

    protected function fireEvent(string $name, ?string $type = null, mixed $data = null): mixed
    {
        $eventData = [
            'session_key'    => $this->driver->getSessionKey(),
            'session_driver' => $this->driver->getDriverName(),
            'session_model'  => $this->driver->getSessionModel(),
        ];

        if ($type) {
            $eventData[$type] = $data;
        }

        return $this->events->dispatch('LaravelCart.' . $name, [$eventData, $this], true);
    }
}
