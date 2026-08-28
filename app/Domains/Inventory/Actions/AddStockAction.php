<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Batch;
use App\Domains\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class AddStockAction
{
    public function execute(string $productId, string $warehouseId, float $quantity, float $purchaseCost, ?string $batchNumber = null, ?string $expiryDate = null): Batch
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $purchaseCost, $batchNumber, $expiryDate) {
            $batch = Batch::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'batch_number' => $batchNumber ?? ('BATCH-' . strtoupper(uniqid())),
                'purchase_cost' => $purchaseCost,
                'stock_qty' => $quantity,
                'expiry_date' => $expiryDate,
            ]);

            StockMovement::create([
                'batch_id' => $batch->id,
                'warehouse_id' => $warehouseId,
                'type' => 'in',
                'quantity' => $quantity,
            ]);

            return $batch;
        });
    }
}