<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Actions;

use App\Domains\Finance\Models\Account;
use App\Domains\Finance\Models\JournalEntry;
use App\Domains\POS\Models\Order;
use App\Domains\Reporting\Models\DailySalesSummary;
use Carbon\Carbon;

class GenerateDailySalesReportAction
{
    public function execute(string $storeId, ?string $date = null): DailySalesSummary
    {
        $targetDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $orders = Order::where('store_id', $storeId)
            ->whereDate('created_at', $targetDate)
            ->where('status', 'completed')
            ->get();

        $totalSales = (float) $orders->sum('grand_total');
        $totalTransactions = $orders->count();
        $orderIds = $orders->pluck('id');

        // دەرهێنانی تێچوو لە ڕێگەی دەفتەری ڕۆژنامەی ژمێریاری
        $cogsAccount = Account::where('code', '5010')->first();
        $totalCost = 0.0;

        if ($cogsAccount && $orderIds->isNotEmpty()) {
            $journalEntries = JournalEntry::whereIn('order_id', $orderIds)->with('lines')->get();
            foreach ($journalEntries as $entry) {
                $totalCost += (float) $entry->lines
                    ->where('account_id', $cogsAccount->id)
                    ->sum('debit');
            }
        }

        $grossProfit = $totalSales - $totalCost;

        return DailySalesSummary::updateOrCreate(
            [
                'store_id' => $storeId,
                'report_date' => $targetDate,
            ],
            [
                'total_sales' => $totalSales,
                'total_gross_profit' => $grossProfit,
                'total_transactions' => $totalTransactions,
            ]
        );
    }
}
