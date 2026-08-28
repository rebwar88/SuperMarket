<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\StockReservation;

class ReserveStockAction
{
    public function execute(string $productId, ?string $orderId, float $quantity, int $minutes = 15): StockReservation
    {
        return StockReservation::create([
            'product_id' => $productId,
            'order_id' => $orderId,
            'quantity' => $quantity,
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }
}