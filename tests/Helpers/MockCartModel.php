<?php

namespace Wearepixel\Cart\Tests\Helpers;

use Illuminate\Database\Eloquent\Model;

class MockCartModel extends Model
{
    protected $fillable = [
        'session_id',
        'items',
        'conditions',
    ];

    public $timestamps = false;

    protected $casts = [
        'items' => 'array',
        'conditions' => 'array',
    ];

    protected $table = 'carts';
}
