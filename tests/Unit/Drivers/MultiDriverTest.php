<?php

declare(strict_types=1);

use Wearepixel\Cart\Drivers\MultiDriver;
use Wearepixel\Cart\Drivers\NullDriver;

describe('MultiDriver', function () {
    beforeEach(function () {
        $this->primary = new NullDriver('test-session');
        $this->secondary = new NullDriver('test-session');
        $this->driver = new MultiDriver([$this->primary, $this->secondary]);
    });

    test('returns session key from primary driver', function () {
        expect($this->driver->getSessionKey())->toBe('test-session');
    });

    test('returns multi as driver name', function () {
        expect($this->driver->getDriverName())->toBe('multi');
    });

    test('reads items from primary driver', function () {
        $this->primary->putItems([['id' => 1]]);
        $this->secondary->putItems([['id' => 2]]);

        expect($this->driver->getItems())->toBe([['id' => 1]]);
    });

    test('writes items to all drivers', function () {
        $this->driver->putItems([['id' => 1]]);

        expect($this->primary->getItems())->toBe([['id' => 1]]);
        expect($this->secondary->getItems())->toBe([['id' => 1]]);
    });

    test('reads conditions from primary driver', function () {
        $this->primary->putConditions([['name' => 'GST']]);
        $this->secondary->putConditions([['name' => 'Other']]);

        expect($this->driver->getConditions())->toBe([['name' => 'GST']]);
    });

    test('writes conditions to all drivers', function () {
        $this->driver->putConditions([['name' => 'GST']]);

        expect($this->primary->getConditions())->toBe([['name' => 'GST']]);
        expect($this->secondary->getConditions())->toBe([['name' => 'GST']]);
    });

    test('clearItems clears items on all drivers', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->clearItems();

        expect($this->primary->getItems())->toBe([]);
        expect($this->secondary->getItems())->toBe([]);
    });

    test('flush flushes all drivers', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->putConditions([['name' => 'GST']]);
        $this->driver->flush();

        expect($this->primary->getItems())->toBe([]);
        expect($this->primary->getConditions())->toBe([]);
        expect($this->secondary->getItems())->toBe([]);
        expect($this->secondary->getConditions())->toBe([]);
    });

    test('setSessionKey updates all drivers', function () {
        $this->driver->setSessionKey('new-key');

        expect($this->primary->getSessionKey())->toBe('new-key');
        expect($this->secondary->getSessionKey())->toBe('new-key');
    });

    test('throws when constructed with no drivers', function () {
        expect(fn() => new MultiDriver([]))->toThrow(InvalidArgumentException::class);
    });
});
