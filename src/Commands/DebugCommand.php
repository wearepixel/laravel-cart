<?php

declare(strict_types=1);

namespace Wearepixel\Cart\Commands;

use Illuminate\Console\Command;
use Wearepixel\Cart\Cart;

class DebugCommand extends Command
{
    protected $signature = 'cart:debug';

    protected $description = 'Dump the current cart state. Only runs outside production.';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('cart:debug is not available in production.');
            return self::FAILURE;
        }

        $cart = app('cart');
        $data = static::debugData($cart);

        $this->info('Cart Debug');
        $this->line('');
        $this->line("  Driver:         <comment>{$data['driver']}</comment>");
        $this->line("  Items:          <comment>{$data['item_count']}</comment>");
        $this->line("  Total Quantity: <comment>{$data['total_quantity']}</comment>");
        $this->line("  Subtotal:       <comment>{$data['subtotal']}</comment>");
        $this->line("  Total:          <comment>{$data['total']}</comment>");

        if (! empty($data['conditions'])) {
            $this->line("  Conditions:     <comment>" . implode(', ', $data['conditions']) . '</comment>');
        } else {
            $this->line('  Conditions:     <comment>none</comment>');
        }

        if (! empty($data['items'])) {
            $this->line('');
            $this->table(['ID', 'Name', 'Price', 'Qty'], $data['items']);
        }

        return self::SUCCESS;
    }

    public static function debugData(Cart $cart): array
    {
        $items = $cart->getContent();

        return [
            'driver'         => $cart->getDriver()->getDriverName(),
            'item_count'     => $items->count(),
            'total_quantity' => (int) $cart->getTotalQuantity(),
            'subtotal'       => (float) $cart->getSubTotal(false),
            'total'          => (float) $cart->getTotal(),
            'conditions'     => $cart->getConditions()->map(fn($c) => $c->getName())->values()->toArray(),
            'items'          => $items->map(fn($item) => [
                $item->id,
                $item->name,
                $item->price,
                $item->quantity,
            ])->values()->toArray(),
        ];
    }
}
