<?php

declare(strict_types=1);

use Wearepixel\Cart\Drivers\SessionDriver;
use Wearepixel\Cart\Tests\Helpers\SessionMock;

describe('SessionDriver', function () {
    beforeEach(function () {
        $this->session = new SessionMock;
        $this->driver = new SessionDriver($this->session, 'test-session');
    });

    test('returns its session key', function () {
        expect($this->driver->getSessionKey())->toBe('test-session');
    });

    test('returns session as driver name', function () {
        expect($this->driver->getDriverName())->toBe('session');
    });

    test('starts with empty items', function () {
        expect($this->driver->getItems())->toBe([]);
    });

    test('stores and retrieves items', function () {
        $items = [['id' => 1, 'name' => 'Widget', 'price' => 10.0]];
        $this->driver->putItems($items);
        expect($this->driver->getItems())->toBe($items);
    });

    test('starts with empty conditions', function () {
        expect($this->driver->getConditions())->toBe([]);
    });

    test('stores and retrieves conditions', function () {
        $conditions = [['name' => 'GST', 'type' => 'tax', 'value' => '10%']];
        $this->driver->putConditions($conditions);
        expect($this->driver->getConditions())->toBe($conditions);
    });

    test('clearItems removes items from session', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->putConditions([['name' => 'GST']]);
        $this->driver->clearItems();

        expect($this->driver->getItems())->toBe([]);
        expect($this->driver->getConditions())->toBe([['name' => 'GST']]);
    });

    test('flush removes items and conditions from session', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->putConditions([['name' => 'GST']]);
        $this->driver->flush();

        expect($this->driver->getItems())->toBe([]);
        expect($this->driver->getConditions())->toBe([]);
    });

    test('setSessionKey migrates data to new key', function () {
        $this->driver->putItems([['id' => 1]]);
        $this->driver->putConditions([['name' => 'GST']]);
        $this->driver->setSessionKey('new-key');

        expect($this->driver->getSessionKey())->toBe('new-key');
        expect($this->driver->getItems())->toBe([['id' => 1]]);
        expect($this->driver->getConditions())->toBe([['name' => 'GST']]);
    });

    test('getSessionModel returns null', function () {
        expect($this->driver->getSessionModel())->toBeNull();
    });
});
