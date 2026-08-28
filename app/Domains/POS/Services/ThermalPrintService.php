<?php

declare(strict_types=1);

namespace App\Domains\POS\Services;

use App\Domains\POS\Models\Order;
use Exception;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ThermalPrintService
{
    /**
     * ناردنی فەرمانی چاپ و کردنەوەی سندوقی پارە
     */
    public function printOrder(Order $order, ?string $printerTarget = null): bool
    {
        try {
            // ئەگەر ئایپی بێت یان پرینتەری ویندۆز
            if ($printerTarget && filter_var($printerTarget, FILTER_VALIDATE_IP)) {
                $connector = new NetworkPrintConnector($printerTarget, 9100);
            } else {
                $printerName = $printerTarget ?: "POS-80";
                $connector = new WindowsPrintConnector($printerName);
            }

            $printer = new Printer($connector);

            // ١. کردنەوەی سندوقی پارە (Cash Drawer Kick)
            $printer->pulse();

            // ٢. سەرپەڕەی پسوولە
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
            $printer->text("SUPERMARKET\n");
            $printer->selectPrintMode();
            $printer->text("سوپەرمارکێتی کوردی\n");
            $printer->text("--------------------------------\n");

            // ٣. زانیاریی پسوولە
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Invoice: " . $order->invoice_number . "\n");
            $printer->text("Date: " . $order->created_at->format('Y-m-d H:i') . "\n");
            $printer->text("--------------------------------\n");

            // ٤. لیستی کاڵاکان
            foreach ($order->items as $item) {
                $line = sprintf(
                    "%-16s %2d x %-6s\n",
                    substr($item->product->name ?? 'Item', 0, 16),
                    (int) $item->quantity,
                    number_format((float) $item->total_price, 0)
                );
                $printer->text($line);
            }

            $printer->text("--------------------------------\n");

            // ٥. کۆی گشتی
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("Total: " . number_format((float) $order->grand_total, 0) . " IQD\n");
            $printer->text("Paid:  " . number_format((float) $order->paid_amount, 0) . " IQD\n");
            $printer->text("Change:" . number_format((float) $order->change_due, 0) . " IQD\n");
            $printer->selectPrintMode();

            // ٦. هێمای کۆتایی و بڕین (Cut)
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\nسوپاس بۆ سەردانەکەت\n\n");
            $printer->cut();
            $printer->close();

            return true;
        } catch (Exception $e) {
            // لە حاڵەتی نەبوونی پرینتەری فیزیکی، بە بێ شکان تێدەپەڕێت
            logger()->error('Thermal Print Error: ' . $e->getMessage());
            return false;
        }
    }
}
