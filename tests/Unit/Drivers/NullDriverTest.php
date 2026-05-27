<?php

declare(strict_types=1);

use Wearepixel\Cart\Drivers\NullDriver;

describe('NullDriver', function () {
    beforeEach(function () {
        $this->driver = new NullDriver('test-session');
    });

    test('returns its session key', function () {
        expect($this->driver->getSessionKey())->toBe('test-session');
    });

    test('returns \'null\' as driver name', function () {
        expect($this->driver->getDriverName())->toBe('null');
    });

    test('starts with empty items', function () {
        expect($this->driver->getItems())->toBe([]);
    });

    test('stores and retrieves items', function () {
        $this->driver->putItems([['id' => 1, 'name' => 'Widget']]);
        expect($this->driver->getItems())->toBe([['id' => 1, 'name' => 'Widget']]);
    });

    test('starts with empty conditions', function () {
        expect($this->driver->getConditions())->toBe([]);
    });

    test('stores and retrieves conditions', function () {
        $this->driver->putConditions([['name' => 'GST', 'type' => 'tax', 'value' => '10%']]);
        expect($this->driver->getConditions())->toBe([['name' => 'GST', 'type' => 'tax', 'value' => '10%']]);
    });

    test('clearItems empties items but keeps conditions', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->putConditions([['name' => 'GST']]);
        $this->driver->clearItems();

        expect($this->driver->getItems())->toBe([]);
        expect($this->driver->getConditions())->toBe([['name' => 'GST']]);
    });

    test('flush empties items and conditions', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->putConditions([['name' => 'GST']]);
        $this->driver->flush();

        expect($this->driver->getItems())->toBe([]);
        expect($this->driver->getConditions())->toBe([]);
    });

    test('setSessionKey updates the key', function () {
        $this->driver->setSessionKey('new-key');
        expect($this->driver->getSessionKey())->toBe('new-key');
    });

    test('getSessionModel returns null', function () {
        expect($this->driver->getSessionModel())->toBeNull();
    });
});
