<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Batch;
use App\Domains\Inventory\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class DeductStockAction
{
    public function execute(string $batchId, float $quantity, string $reason = 'manual_deduction'): Batch
    {
        return DB::transaction(function () use ($batchId, $quantity, $reason) {
            $batch = Batch::where('id', $batchId)->lockForUpdate()->first();

            if (!$batch) {
                throw new Exception("وەجبەی داواکراو نەدۆزرایەوە.");
            }

            if ((float) $batch->stock_qty < $quantity) {
                throw new Exception("بڕی بەردەست لەم وەجبەیە بەشی ئەم کەمکردنەوەیە ناکات.");
            }

            $batch->decrement('stock_qty', $quantity);

            StockMovement::create([
                'batch_id' => $batch->id,
                'warehouse_id' => $batch->warehouse_id,
                'type' => 'out',
                'quantity' => -$quantity,
            ]);

            return $batch;
        });
    }
}