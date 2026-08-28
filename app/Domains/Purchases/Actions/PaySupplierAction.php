<?php

declare(strict_types=1);

namespace App\Domains\Purchases\Actions;

use App\Domains\Finance\Actions\RecordJournalEntryAction;
use App\Domains\Finance\DTOs\JournalEntryData;
use App\Domains\Finance\Models\Account;
use App\Domains\Purchases\Models\Supplier;
use App\Domains\Purchases\Models\SupplierLedger;
use App\Domains\Purchases\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

class PaySupplierAction
{
    public function __construct(
        private readonly RecordJournalEntryAction $recordJournalEntryAction
    ) {}

    public function execute(string $supplierId, float $amount, string $paymentMethod = 'cash'): SupplierPayment
    {
        return DB::transaction(function () use ($supplierId, $amount, $paymentMethod) {
            $supplier = Supplier::findOrFail($supplierId);

            $payment = SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
            ]);

            // کەمکردنەوەی قەرز
            $supplier->decrement('total_balance', $amount);

            // تۆمار لە دەفتەری حسابی دابینکەر
            SupplierLedger::create([
                'supplier_id' => $supplier->id,
                'entry_type' => 'payment',
                'amount' => $amount,
                'running_balance' => $supplier->fresh()->total_balance,
            ]);

            // قەیدی ژمێریاری دەرچوونی کاش
            $apAcc = Account::where('code', '2010')->first();
            $cashAcc = Account::where('code', '1010')->first();

            if ($apAcc && $cashAcc) {
                $this->recordJournalEntryAction->execute(
                    JournalEntryData::fromArray([
                        'purchase_order_id' => null,
                        'source_type' => 'supplier_payment',
                        'lines' => [
                            ['account_id' => $apAcc->id, 'debit' => $amount, 'credit' => 0.00],
                            ['account_id' => $cashAcc->id, 'debit' => 0.00, 'credit' => $amount],
                        ]
                    ])
                );
            }

            return $payment;
        });
    }
}
