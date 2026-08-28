<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Finance\Models\Account;
use App\Domains\Finance\Models\JournalEntry;
use App\Domains\Finance\Models\JournalEntryLine;
use App\Domains\Inventory\Models\Batch;
use App\Domains\Inventory\Models\StockMovement;
use App\Domains\Inventory\Models\Wastage;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateWastageAction
{
    public function execute(string $productId, string $batchId, float $quantity, string $reason): Wastage
    {
        return DB::transaction(function () use ($productId, $batchId, $quantity, $reason) {
            $batch = Batch::where('id', $batchId)->lockForUpdate()->first();

            if (!$batch || (float) $batch->stock_qty < $quantity) {
                throw new Exception("بڕی تەلەفکراو زیاترە لە ستۆکی بەردەستی وەجبەکە.");
            }

            $batch->decrement('stock_qty', $quantity);

            $wastage = Wastage::create([
                'product_id' => $productId,
                'batch_id' => $batchId,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);

            StockMovement::create([
                'batch_id' => $batch->id,
                'warehouse_id' => $batch->warehouse_id,
                'type' => 'wastage',
                'quantity' => -$quantity,
            ]);

            // تۆمارکردنی قەیدی زیانی تەلەف لە ژمێریاری
            $this->createAccountingEntry($batch, $quantity);

            return $wastage;
        });
    }

    private function createAccountingEntry(Batch $batch, float $quantity): void
    {
        $wastageCost = round($quantity * (float) $batch->purchase_cost, 2);
        if ($wastageCost <= 0) return;

        $wastageAcc = Account::where('code', '5020')->first();
        $inventoryAcc = Account::where('code', '1040')->first();

        if ($wastageAcc && $inventoryAcc) {
            $entry = JournalEntry::create([
                'source_type' => 'inventory_wastage',
                'posted_at' => now(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $wastageAcc->id,
                'debit' => $wastageCost,
                'credit' => 0.00,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $inventoryAcc->id,
                'debit' => 0.00,
                'credit' => $wastageCost,
            ]);
        }
    }
}