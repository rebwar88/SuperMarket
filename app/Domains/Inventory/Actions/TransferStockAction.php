<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Batch;
use App\Domains\Inventory\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class TransferStockAction
{
    public function execute(string $sourceBatchId, string $targetWarehouseId, float $quantity): Batch
    {
        return DB::transaction(function () use ($sourceBatchId, $targetWarehouseId, $quantity) {
            $sourceBatch = Batch::where('id', $sourceBatchId)->lockForUpdate()->first();

            if (!$sourceBatch) {
                throw new Exception("وەجبەی سەرچاوە نەدۆزرایەوە.");
            }

            if ((float) $sourceBatch->stock_qty < $quantity) {
                throw new Exception("بڕی پێویست لە مەخزەنی سەرچاوەدا بوونی نییە بۆ گواستنەوە.");
            }

            // ١. دەرکردن لە مەخزەنی یەکەم
            $sourceBatch->decrement('stock_qty', $quantity);

            StockMovement::create([
                'batch_id' => $sourceBatch->id,
                'warehouse_id' => $sourceBatch->warehouse_id,
                'type' => 'transfer_out',
                'quantity' => -$quantity,
            ]);

            // ٢. زیادکردن بۆ مەخزەنی دووەم
            $targetBatch = Batch::firstOrCreate(
                [
                    'product_id' => $sourceBatch->product_id,
                    'warehouse_id' => $targetWarehouseId,
                    'batch_number' => $sourceBatch->batch_number,
                ],
                [
                    'purchase_cost' => $sourceBatch->purchase_cost,
                    'expiry_date' => $sourceBatch->expiry_date,
                    'stock_qty' => 0,
                ]
            );

            $targetBatch->increment('stock_qty', $quantity);

            StockMovement::create([
                'batch_id' => $targetBatch->id,
                'warehouse_id' => $targetWarehouseId,
                'type' => 'transfer_in',
                'quantity' => $quantity,
            ]);

            return $targetBatch;
        });
    }
}