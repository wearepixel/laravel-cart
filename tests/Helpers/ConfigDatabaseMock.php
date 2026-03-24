<?php

return [
    'format_numbers' => true,
    'decimals' => 2,
    'round_mode' => 'up',
    'driver' => 'database',
    'storage' => [
        'session',
        'database' => [
            'model' => \Wearepixel\Cart\Tests\Helpers\MockCartModel::class,
            'id' => 'session_id',
            'items' => 'items',
            'conditions' => 'conditions',
        ],
    ],
];
