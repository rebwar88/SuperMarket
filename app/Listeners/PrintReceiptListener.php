<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Infrastructure\Hardware\CustomerDisplays\CustomerPoleDisplayDriver;
use App\Infrastructure\Hardware\Printers\EscPosPrinterDriver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PrintReceiptListener implements ShouldQueue
{
    public function __construct(
        private readonly EscPosPrinterDriver $printer,
        private readonly CustomerPoleDisplayDriver $poleDisplay
    ) {}

    public function handle(OrderCompleted $event): void
    {
        try {
            $order = $event->order->load(['items.product', 'user', 'store']);

            // دروستکردنی ڕێنماییەکانی چاپ
            $receiptData = $this->printer->printReceipt($order, $order->store?->name ?? 'مارکێتی کوردی');

            // ناردنی کۆی گشتی بۆ شاشەی کڕیار
            $displayData = $this->poleDisplay->showTotal((float) $order->grand_total);

            Log::info("Hardware commands dispatched for Order: {$order->invoice_number}", [
                'order_id' => $order->id,
                'receipt_bytes' => strlen($receiptData),
                'display_command' => $displayData,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to process hardware output: " . $e->getMessage(), [
                'order_id' => $event->order->id ?? null,
            ]);
        }
    }
}
