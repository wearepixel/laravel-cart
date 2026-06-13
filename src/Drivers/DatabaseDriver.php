<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Drivers;

use Illuminate\Database\Eloquent\Model;
use Wearepixel\Cart\Drivers\Contracts\CartDriver;

class DatabaseDriver implements CartDriver
{
    private Model $model;

    public function __construct(
        private string $modelClass,
        private string $idColumn,
        private string $itemsColumn,
        private string $conditionsColumn,
        private string $sessionKey,
    ) {
        $this->model = (new $modelClass)->firstOrNew([$idColumn => $sessionKey]);
        $this->model[$itemsColumn] ??= [];
        $this->model[$conditionsColumn] ??= [];
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
        $this->model[$this->idColumn] = $sessionKey;
        $this->model->save();
    }

    public function getDriverName(): string
    {
        return 'database';
    }

    public function getItems(): array
    {
        return $this->model[$this->itemsColumn] ?? [];
    }

    public function putItems(array $items): void
    {
        $this->model[$this->itemsColumn] = $items;
        $this->model->save();
    }

    public function getConditions(): array
    {
        return $this->model[$this->conditionsColumn] ?? [];
    }

    public function putConditions(array $conditions): void
    {
        $this->model[$this->conditionsColumn] = $conditions;
        $this->model->save();
    }

    public function clearItems(): void
    {
        $this->model[$this->itemsColumn] = [];
        $this->model->save();
    }

    public function flush(): void
    {
        $this->model->delete();
        $this->model[$this->itemsColumn] = [];
        $this->model[$this->conditionsColumn] = [];
    }

    public function getSessionModel(): mixed
    {
        return $this->model;
    }
}
