<?php

namespace Wearepixel\Cart\Helpers;

class Helpers
{
    /**
     * Normalize prices
     */
    public static function normalizePrice($price): float
    {
        return (is_string($price)) ? floatval($price) : $price;
    }

    /**
     * Get the rounding mode
     */
    public static function roundMode($config): int
    {
        if (isset($config['round_mode']) && $config['round_mode'] == 'up') {
            return PHP_ROUND_HALF_UP;
        }

        return PHP_ROUND_HALF_DOWN;
    }

    /**
     * check if array is multi dimensional array
     * This will only check the first element of the array if it is still an array
     * to decide that it is a multi dimensional, if you want to check the array strictly
     * with all on its element, flag the second argument as true
     *
     * @param  bool  $recursive
     * @return bool
     */
    public static function isMultiArray($array, $recursive = false)
    {
        if ($recursive) {
            return (count($array) == count($array, COUNT_RECURSIVE)) ? false : true;
        } else {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    return true;
                } else {
                    return false;
                }
            }

        }
    }

    /**
     * check if variable is set and has value, return a default value
     *
     * @param  bool|mixed  $default
     * @return bool|mixed
     */
    public static function issetAndHasValueOrAssignDefault(&$var, $default = false)
    {
        if ((isset($var)) && ($var != '')) {
            return $var;
        }

        return $default;
    }

    public static function formatValue($value, $formatNumber, $config)
    {
        if (! $value) {
            return 0;
        }

        if ($formatNumber || $config['format_numbers']) {
            return round($value, $config['decimals'], self::roundMode($config));
        } else {
            return floatval($value);
        }
    }
}
