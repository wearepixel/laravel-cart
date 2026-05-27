<?php

declare(strict_types=1);

use Wearepixel\Cart\Drivers\DatabaseDriver;
use Wearepixel\Cart\Tests\Helpers\MockCartModel;

beforeEach(function () {
    $this->driver = new DatabaseDriver(
        MockCartModel::class,
        'session_id',
        'items',
        'conditions',
        'test-session',
    );
});

describe('DatabaseDriver', function () {
    test('returns its session key', function () {
        expect($this->driver->getSessionKey())->toBe('test-session');
    });

    test('returns database as driver name', function () {
        expect($this->driver->getDriverName())->toBe('database');
    });

    test('starts with empty items', function () {
        expect($this->driver->getItems())->toBe([]);
    });

    test('stores and retrieves items', function () {
        $items = [['id' => 1, 'name' => 'Widget']];
        $this->driver->putItems($items);

        $fresh = new DatabaseDriver(MockCartModel::class, 'session_id', 'items', 'conditions', 'test-session');
        expect($fresh->getItems())->toBe($items);
    });

    test('starts with empty conditions', function () {
        expect($this->driver->getConditions())->toBe([]);
    });

    test('stores and retrieves conditions', function () {
        $conditions = [['name' => 'GST', 'type' => 'tax', 'value' => '10%']];
        $this->driver->putConditions($conditions);

        $fresh = new DatabaseDriver(MockCartModel::class, 'session_id', 'items', 'conditions', 'test-session');
        expect($fresh->getConditions())->toBe($conditions);
    });

    test('flush deletes the database record', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->flush();

        expect(MockCartModel::count())->toBe(0);
    });

    test('clearItems empties items but keeps conditions', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->putConditions([['name' => 'GST']]);
        $this->driver->clearItems();

        $fresh = new DatabaseDriver(MockCartModel::class, 'session_id', 'items', 'conditions', 'test-session');
        expect($fresh->getItems())->toBe([]);
        expect($fresh->getConditions())->toBe([['name' => 'GST']]);
    });

    test('getSessionModel returns the Eloquent model', function () {
        expect($this->driver->getSessionModel())->toBeInstanceOf(MockCartModel::class);
    });
});
