<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Actions;

use App\Domains\Inventory\Models\Batch;
use App\Domains\POS\Models\OrderItem;
use App\Domains\Reporting\Models\ProductProfitabilitySnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GenerateProfitMarginReportAction
{
    /**
     * دروستکردنی ڕاپۆرتی قازانجی کاڵاکان
     * @return Collection<ProductProfitabilitySnapshot>
     */
    public function execute(?string $date = null): Collection
    {
        $targetDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $soldItems = OrderItem::whereHas('order', function ($query) use ($targetDate) {
            $query->whereDate('created_at', $targetDate)
                  ->where('status', 'completed');
        })->with('product')->get();

        $groupedByProduct = $soldItems->groupBy('product_id');
        $snapshots = collect();

        foreach ($groupedByProduct as $productId => $items) {
            $unitsSold = (float) $items->sum('quantity');
            $totalRevenue = (float) $items->sum('total_price');

            // تێچووی کڕین (Purchase Cost) لە دوایین وەجبە یان تێکڕای تێچووی کڕین
            $avgPurchaseCost = (float) Batch::where('product_id', $productId)->avg('purchase_cost');
            $totalCost = $unitsSold * $avgPurchaseCost;

            $grossProfit = $totalRevenue - $totalCost;
            $marginPercent = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0.00;

            $snapshot = ProductProfitabilitySnapshot::updateOrCreate(
                [
                    'product_id' => $productId,
                    'snapshot_date' => $targetDate,
                ],
                [
                    'units_sold' => $unitsSold,
                    'margin_percent' => $marginPercent,
                ]
            );

            $snapshots->push($snapshot);
        }

        return $snapshots;
    }
}
