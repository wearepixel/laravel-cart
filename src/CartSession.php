<?php

namespace Wearepixel\Cart;

class CartSession
{
    private $customStorage;

    public $sessionKey;
    public $driver;

    private $session;

    private $sessionId = null;
    private $itemsKey = null;
    private $conditionsKey = null;

    public function __construct($session, $sessionKey, $config)
    {
        $this->sessionKey = $sessionKey;
        $this->driver = $config['driver'];

        if ($this->driver === 'session') {
            $this->customStorage = false;
            $this->session = $session;
            $this->itemsKey = $this->sessionKey . '_cart_items';
            $this->conditionsKey = $this->sessionKey . '_cart_conditions';
        } elseif ($this->driver === 'database') {
            $this->customStorage = true;

            $this->sessionId = $config['storage']['database']['id'];
            $this->itemsKey = $config['storage']['database']['items'];
            $this->conditionsKey = $config['storage']['database']['conditions'];

            $this->session = $session->firstOrNew([$this->sessionId => $this->sessionKey]);

            $this->session[$this->itemsKey] = $this->session[$this->itemsKey] ?? [];
            $this->session[$this->conditionsKey] = $this->session[$this->conditionsKey] ?? [];
        }
    }

    public function isInternalSession($session)
    {
        return in_array(is_object($session) ? get_class($session) : $session, [
            'Illuminate\Session\SessionManager',
            'Wearepixel\Cart\Tests\Helpers\SessionMock',
        ]);
    }

    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey)
    {
        // set the session key and then update the session
        $this->sessionKey = $sessionKey;

        if ($this->driver == 'session') {
            $session = $this->session->get($this->itemsKey);
            $sessionKey = $this->session->get($this->conditionsKey);

            $this->session->forget($this->itemsKey);
            $this->session->forget($this->conditionsKey);

            $this->itemsKey = $this->sessionKey . '_cart_items';
            $this->conditionsKey = $this->sessionKey . '_cart_conditions';

            $this->session->put($this->itemsKey, $session);
            $this->session->put($this->conditionsKey, $sessionKey);
        } elseif ($this->driver == 'database') {
            $this->session[$this->sessionId] = $sessionKey;
            $this->session->save();
        }
    }

    public function has($key)
    {
        return $this->session->has($key) ?? $this->session;
    }

    public function getItems()
    {
        return $this->driver == 'session' ? $this->session->get($this->itemsKey) : $this->session[$this->itemsKey];
    }

    public function getConditions()
    {
        return $this->driver == 'session' ? $this->session->get($this->conditionsKey) : $this->session[$this->conditionsKey];
    }

    public function putItems($value)
    {
        if ($this->driver == 'session') {
            return $this->session->put($this->itemsKey, $value);
        }

        // convert any conditions to arrays
        $value = is_array($value) ? $value : $value->toArray();

        foreach ($value as $key => $item) {
            if ($item['conditions']) {
                if (is_array($item['conditions'])) {
                    $value[$key]['conditions'] = collect($item['conditions'])->map(function ($condition) {
                        return $condition->toArray();
                    });
                } else {
                    $value[$key]['conditions'] = $item['conditions']->toArray();
                }
            }
        }

        $this->session[$this->itemsKey] = $value;
        $this->session->save();

        return $this->session;
    }

    public function putConditions($value)
    {
        if ($this->driver == 'session') {
            return $this->session->put($this->conditionsKey, $value);
        }

        if (! empty($value)) {
            $value = $value->mapWithKeys(function ($condition) {
                return [$condition->getName() => $condition->toArray()];
            });
        }

        $this->session[$this->conditionsKey] = $value;
        $this->session->save();

        return $this->session;
    }

    public function clearItems()
    {
        if ($this->driver == 'session') {
            $this->session->forget($this->itemsKey);

            return true;
        }

        $this->session[$this->itemsKey] = [];

        return $this->session->save();
    }

    public function clear()
    {
        if ($this->driver == 'session') {
            $this->session->forget($this->itemsKey);
            $this->session->forget($this->conditionsKey);

            return true;
        }

        $deleted = $this->session->delete();

        $this->session[$this->itemsKey] = [];
        $this->session[$this->conditionsKey] = [];

        return $deleted;
    }

    public function getSessionModel()
    {
        return $this->session;
    }
}
