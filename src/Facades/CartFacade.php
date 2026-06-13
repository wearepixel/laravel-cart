<?php

namespace Wearepixel\Cart\Facades;

use Illuminate\Support\Facades\Facade;
use Wearepixel\Cart\Cart;
use Wearepixel\Cart\CartCollection;
use Wearepixel\Cart\CartCondition;
use Wearepixel\Cart\CartConditionCollection;
use Wearepixel\Cart\Coupons\Coupon;
use Wearepixel\Cart\Drivers\Contracts\CartDriver;
use Wearepixel\Cart\ItemCollection;
use Wearepixel\Cart\Shipping\ShippingRate;
use Wearepixel\Cart\Tax\TaxRule;
use Wearepixel\Cart\Testing\CartFactory;

/**
 * @mixin Cart
 *
 * @method static CartDriver getDriver()
 * @method static CartFactory fake()
 * @method static void assertContains(int|string $id)
 * @method static void assertCount(int $count)
 * @method static void assertTotalQuantity(int|float $quantity)
 * @method static void assertSubTotal(float $amount)
 * @method static void assertTotal(float $amount)
 * @method static void assertConditionApplied(string $conditionName)
 * @method static void assertEmpty()
 * @method static void assertNotEmpty()
 * @method static string getSessionKey()
 * @method static void setSessionKey(string $sessionKey)
 * @method static string getInstanceName()
 * @method static ItemCollection|null get(int|string $itemId)
 * @method static bool has(int|string $itemId)
 * @method static Cart add(string|int|array $id, string|null $name = null, float|null $price = null, int|float|null $quantity = null, array $attributes = [], CartCondition|array $conditions = [], string|null $associatedModel = null)
 * @method static bool removeInvalidItems()
 * @method static bool update(int|string $id, array $data)
 * @method static void setConfig(array $config)
 * @method static Cart addItemCondition(int|string $productId, CartCondition $itemCondition)
 * @method static bool remove(int|string $id)
 * @method static bool clear()
 * @method static bool clearItems()
 * @method static Cart condition(array|CartCondition $condition)
 * @method static Cart coupon(Coupon $coupon)
 * @method static Cart tax(TaxRule $taxRule)
 * @method static Cart shipping(ShippingRate $shippingRate)
 * @method static CartConditionCollection|array getConditions(bool $array = false, bool $active = false)
 * @method static CartCondition|null getCondition(string $conditionName)
 * @method static CartConditionCollection getConditionsByType(string $type)
 * @method static void removeConditionsByType(string $type)
 * @method static void removeCartCondition(string $conditionName)
 * @method static bool removeItemCondition(int|string $itemId, string $conditionName)
 * @method static bool clearItemConditions(int|string $itemId)
 * @method static void clearCartConditions()
 * @method static void clearAllConditions()
 * @method static int|float|string getSubTotalWithoutConditions(bool $formatted = true)
 * @method static int|float|string getSubTotal(bool $formatted = true)
 * @method static int|float getTotal()
 * @method static int|float getCalculatedValueForCondition(string $conditionName)
 * @method static int|float getTotalQuantity()
 * @method static CartCollection getContent()
 * @method static bool isEmpty()
 * @method static Cart associate(mixed $model)
 */
class CartFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }
}
