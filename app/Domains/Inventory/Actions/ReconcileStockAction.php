<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Batch;
use App\Domains\Inventory\Models\StockCount;
use App\Domains\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ReconcileStockAction
{
    public function execute(StockCount $stockCount): void
    {
        DB::transaction(function () use ($stockCount) {
            foreach ($stockCount->items as $item) {
                $diff = round((float) $item->counted_qty - (float) $item->system_qty, 3);

                if ($diff != 0) {
                    $batch = Batch::where('product_id', $item->product_id)
                        ->where('warehouse_id', $stockCount->warehouse_id)
                        ->latest()
                        ->first();

                    if ($batch) {
                        $batch->increment('stock_qty', $diff);

                        StockMovement::create([
                            'batch_id' => $batch->id,
                            'warehouse_id' => $stockCount->warehouse_id,
                            'type' => 'reconciliation',
                            'quantity' => $diff,
                        ]);
                    }
                }
            }

            $stockCount->update(['status' => 'completed']);
        });
    }
}