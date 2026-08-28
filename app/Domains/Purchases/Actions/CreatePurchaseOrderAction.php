<?php

declare(strict_types=1);

namespace App\Domains\Purchases\Actions;

use App\Domains\Purchases\DTOs\PurchaseOrderData;
use App\Domains\Purchases\Models\PurchaseOrder;
use App\Domains\Purchases\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePurchaseOrderAction
{
    public function execute(PurchaseOrderData $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . strtoupper(Str::random(8)),
                'supplier_id' => $data->supplierId,
                'total_amount' => $data->totalAmount,
                'status' => 'ordered',
            ]);

            foreach ($data->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['purchase_price'] ?? $item['cost_price'],
                ]);
            }

            return $po;
        });
    }
}
