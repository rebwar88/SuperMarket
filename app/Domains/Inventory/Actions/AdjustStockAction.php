<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Batch;
use App\Domains\Inventory\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class AdjustStockAction
{
    public function execute(string $batchId, float $newQuantity, string $reason = 'manual_adjustment'): Batch
    {
        return DB::transaction(function () use ($batchId, $newQuantity, $reason) {
            $batch = Batch::where('id', $batchId)->lockForUpdate()->first();

            if (!$batch) {
                throw new Exception("وەجبەی مەبەست نەدۆزرایەوە.");
            }

            $currentQty = (float) $batch->stock_qty;
            $diff = round($newQuantity - $currentQty, 3);

            $batch->update(['stock_qty' => $newQuantity]);

            StockMovement::create([
                'batch_id' => $batch->id,
                'warehouse_id' => $batch->warehouse_id,
                'type' => 'adjustment',
                'quantity' => $diff,
            ]);

            return $batch;
        });
    }
}