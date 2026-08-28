<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Batch;
use Exception;
use Illuminate\Support\Facades\DB;

class DeductStockFifoAction
{
    /**
     * لێدەرکردنی ستۆک بەپێی کاتی هاتنی وەجبەکان و بەرواری بەسەرچوون (FIFO/FEFO)
     * و گەڕاندنەوەی تێچووی کۆی کاڵا فرۆشراوەکە (Total COGS)
     */
    public function execute(string $productId, string $orderItemId, float $quantity): float
    {
        $remainingQtyToDeduct = $quantity;
        $totalCost = 0.0;

        // وەرگرتنی وەجبە بەردەستەکان بەپێی بەرواری بەسەرچوون و دروستبوون
        $batches = Batch::where('product_id', $productId)
            ->where('stock_qty', '>', 0)
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->orderBy('created_at', 'ASC')
            ->lockForUpdate()
            ->get();

        $totalAvailableStock = $batches->sum('stock_qty');

        if ($totalAvailableStock < $quantity) {
            throw new Exception("بڕی پێویست لە کۆگا بەردەست نییە بۆ کاڵای دیاریکراو.");
        }

        foreach ($batches as $batch) {
            if ($remainingQtyToDeduct <= 0) {
                break;
            }

            $deductQty = min((float) $batch->stock_qty, $remainingQtyToDeduct);

            // کەمکردنەوەی بڕ لە ناو وەجبەکە
            $batch->decrement('stock_qty', $deductQty);

            // تۆمارکردنی تێچووی کاڵاکە
            $costForThisBatch = $deductQty * (float) $batch->purchase_cost;
            $totalCost += $costForThisBatch;

            $remainingQtyToDeduct -= $deductQty;
        }

        return $totalCost;
    }
}
