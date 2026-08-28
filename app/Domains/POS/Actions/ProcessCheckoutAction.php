<?php

declare(strict_types=1);

namespace App\Domains\POS\Actions;

use App\Domains\Finance\Actions\RecordJournalEntryAction;
use App\Domains\Finance\DTOs\JournalEntryData;
use App\Domains\Finance\Models\Account;
use App\Domains\Inventory\Actions\DeductStockFifoAction;
use App\Domains\POS\DTOs\CheckoutData;
use App\Domains\POS\Models\Order;
use App\Domains\POS\Models\OrderItem;
use App\Events\OrderCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessCheckoutAction
{
    public function __construct(
        private readonly DeductStockFifoAction $deductStockAction,
        private readonly RecordJournalEntryAction $recordJournalEntryAction,
    ) {}

    public function execute(CheckoutData $data): Order
    {
        return DB::transaction(function () use ($data) {
            // ١. دروستکردنی ئۆردەر
            $order = Order::create([
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'store_id' => $data->store_id,
                'register_shift_id' => $data->register_shift_id,
                'customer_id' => $data->customer_id,
                'user_id' => $data->user_id,
                'subtotal' => $data->subtotal,
                'discount_amount' => $data->discount_amount,
                'tax_amount' => $data->tax_amount,
                'grand_total' => $data->grand_total,
                'paid_amount' => $data->paid_amount,
                'change_due' => $data->change_due,
                'status' => 'completed',
            ]);

            $totalCost = 0.0;

            // ٢. زیادکردنی کاڵاکان و کەمکردنەوەی ستۆک (FIFO)
            foreach ($data->items as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);

                // کەمکردنەوەی ستۆک لە کۆگای سەرەکی و حیسابکردنی تێچوو
                $cogs = $this->deductStockAction->execute(
                    productId: $item->product_id,
                    orderItemId: $orderItem->id,
                    quantity: (float) $item->quantity
                );

                $totalCost += $cogs;
            }

            // ٣. تۆمارکردنی قەیدی ژمێریاری لە بەشی دارایی
            $cashAccount = Account::where('code', '1010')->first();
            $salesAccount = Account::where('code', '4010')->first();
            $cogsAccount = Account::where('code', '5010')->first();
            $inventoryAccount = Account::where('code', '1040')->first();

            if ($cashAccount && $salesAccount) {
                $lines = [
                    ['account_id' => $cashAccount->id, 'debit' => $data->grand_total, 'credit' => 0],
                    ['account_id' => $salesAccount->id, 'debit' => 0, 'credit' => $data->grand_total],
                ];

                if ($cogsAccount && $inventoryAccount && $totalCost > 0) {
                    $lines[] = ['account_id' => $cogsAccount->id, 'debit' => $totalCost, 'credit' => 0];
                    $lines[] = ['account_id' => $inventoryAccount->id, 'debit' => 0, 'credit' => $totalCost];
                }

                $this->recordJournalEntryAction->execute(
                    JournalEntryData::fromArray([
                        'order_id' => $order->id,
                        'source_type' => 'pos_sale',
                        'lines' => $lines,
                    ])
                );
            }

            // ٤. ناردنی ڕووداوی تەواوبوونی فرۆشتن بۆ چاپ
            OrderCompleted::dispatch($order);

            return $order;
        });
    }
}
