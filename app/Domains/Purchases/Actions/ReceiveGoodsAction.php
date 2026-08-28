<?php

declare(strict_types=1);

namespace App\Domains\Purchases\Actions;

use App\Domains\Finance\Actions\RecordJournalEntryAction;
use App\Domains\Finance\DTOs\JournalEntryData;
use App\Domains\Finance\Models\Account;
use App\Domains\Inventory\Actions\AddStockAction;
use App\Domains\Purchases\Models\GoodsReceivedNote;
use App\Domains\Purchases\Models\PurchaseOrder;
use App\Domains\Purchases\Models\Supplier;
use App\Domains\Purchases\Models\SupplierLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReceiveGoodsAction
{
    public function __construct(
        private readonly AddStockAction $addStockAction,
        private readonly RecordJournalEntryAction $recordJournalEntryAction
    ) {}

    public function execute(string $purchaseOrderId, string $warehouseId, array $receivedItems): GoodsReceivedNote
    {
        return DB::transaction(function () use ($purchaseOrderId, $warehouseId, $receivedItems) {
            $po = PurchaseOrder::findOrFail($purchaseOrderId);

            $grn = GoodsReceivedNote::create([
                'grn_number' => 'GRN-' . strtoupper(Str::random(8)),
                'purchase_order_id' => $po->id,
                'received_at' => now(),
            ]);

            $totalValue = 0.0;

            foreach ($receivedItems as $item) {
                $qty = (float) $item['quantity'];
                $cost = (float) ($item['cost_price'] ?? $item['purchase_cost']);
                $totalValue += ($qty * $cost);

                $this->addStockAction->execute(
                    productId: $item['product_id'],
                    warehouseId: $warehouseId,
                    quantity: $qty,
                    purchaseCost: $cost,
                    batchNumber: $item['batch_number'] ?? ('BATCH-' . strtoupper(Str::random(6))),
                    expiryDate: $item['expiry_date'] ?? null
                );
            }

            $po->update(['status' => 'received']);

            // نوێکردنەوەی قەرزی دابینکەر
            $supplier = Supplier::findOrFail($po->supplier_id);
            $supplier->increment('total_balance', $totalValue);

            // تۆمار لە دەفتەری حسابی دابینکەر (Supplier Ledger)
            SupplierLedger::create([
                'supplier_id' => $supplier->id,
                'entry_type' => 'invoice',
                'amount' => $totalValue,
                'running_balance' => $supplier->fresh()->total_balance,
            ]);

            // قەیدی دارایی ژمێریاری دەبل ئینتری
            $inventoryAcc = Account::where('code', '1040')->first();
            $apAcc = Account::where('code', '2010')->first();

            if ($inventoryAcc && $apAcc) {
                $this->recordJournalEntryAction->execute(
                    JournalEntryData::fromArray([
                        'purchase_order_id' => $po->id,
                        'source_type' => 'goods_received',
                        'lines' => [
                            ['account_id' => $inventoryAcc->id, 'debit' => $totalValue, 'credit' => 0.00],
                            ['account_id' => $apAcc->id, 'debit' => 0.00, 'credit' => $totalValue],
                        ]
                    ])
                );
            }

            return $grn;
        });
    }
}
