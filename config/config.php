<?php

return [
    /*
     * ---------------------------------------------------------------
     * Formatting
     * ---------------------------------------------------------------
     */
    'format_numbers' => env('LARAVEL_CART_FORMAT_VALUES', false),

    'decimals' => env('LARAVEL_CART_DECIMALS', 0),

    'round_mode' => env('LARAVEL_CART_ROUND_MODE', 'down'),

    /*
     * ---------------------------------------------------------------
     * Storage
     * ---------------------------------------------------------------
     * Supported: "session", "database", "redis", "null", "multi"
     */
    'driver' => 'session',

    'storage' => [
        'database' => [
            'model'      => '',
            'id'         => '',
            'items'      => '',
            'conditions' => '',
        ],
    ],

    /*
     * ---------------------------------------------------------------
     * Driver-specific options
     * ---------------------------------------------------------------
     */
    'drivers' => [
        'redis' => [
            'connection' => 'default',
            'ttl'        => 60 * 24 * 7, // 7 days in minutes
        ],

        'multi' => ['session', 'database'],
    ],
];
