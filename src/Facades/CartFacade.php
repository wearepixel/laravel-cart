<?php

namespace Wearepixel\Cart\Facades;

use Illuminate\Support\Facades\Facade;
use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCollection;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\CartConditionCollection;
use Wearepixel\Cart\ItemCollection;

/**
 * @mixin Cart
 *
 * @method static ItemCollection|null get(int|string $itemId)
 * @method static bool has(int|string $itemId)
 * @method static void add(int|string $id, string $name, int|float $price, int $quantity, array $attributes = [], CartCondition|array $conditions = [], string $associatedModel = '')
 * @method static bool update(int|string $id, array $data)
 * @method static void remove(int|string $id)
 * @method static void clear()
 * @method static void clearItems()
 * @method static Cart condition(array|CartCondition $condition)
 * @method static CartConditionCollection|array getConditions(bool $array = false, bool $active = false)
 * @method static CartCondition|null getCondition(string $conditionName)
 * @method static CartConditionCollection getConditionsByType(string $type)
 * @method static void removeConditionsByType(string $type)
 * @method static void removeCartCondition(string $conditionName)
 * @method static void clearItemConditions(int|string $itemId)
 * @method static void clearAllConditions()
 * @method static CartCollection getContent()
 * @method static bool isEmpty()
 * @method static int getTotalQuantity()
 * @method static int|float|string getSubTotal(bool $formatted = true)
 * @method static int|float|string getSubTotalWithoutConditions(bool $formatted = true)
 * @method static int|float getTotal()
 */
class CartFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }
}
