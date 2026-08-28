<?php

declare(strict_types=1);

namespace App\Infrastructure\Hardware\Printers;

use App\Domains\POS\Models\Order;

class EscPosPrinterDriver
{
    private const ESC = "\x1b";
    private const GS = "\x1d";

    public function printReceipt(Order $order, string $storeName = 'مارکێتی کوردی'): string
    {
        $buffer = '';

        // دەستپێکردنەوەی پرینتەر (Initialize)
        $buffer .= self::ESC . "@";

        // ناونیشان لە ناوەڕاست و بە تۆخی (Center & Bold)
        $buffer .= self::ESC . "a" . "\x01";
        $buffer .= self::ESC . "E" . "\x01";
        $buffer .= $storeName . "\n";
        $buffer .= self::ESC . "E" . "\x00";
        $buffer .= "پسوولەی فرۆشتن\n";
        $buffer .= "--------------------------------\n";

        // زانیاریی پسوولە (Align Left)
        $buffer .= self::ESC . "a" . "\x00";
        $buffer .= "ژمارەی پسوولە: " . $order->invoice_number . "\n";
        $buffer .= "بەروار: " . $order->created_at->format('Y-m-d H:i') . "\n";
        $buffer .= "کاشێر: " . ($order->user?->name ?? 'کاشێر') . "\n";
        $buffer .= "--------------------------------\n";

        // سەردێڕی خشتەی کاڵاکان
        $buffer .= sprintf("%-16s %4s %9s\n", "کاڵا", "بڕ", "کۆ");
        $buffer .= "--------------------------------\n";

        // لیستی کاڵاکان
        foreach ($order->items as $item) {
            $name = mb_substr($item->product?->name ?? 'کاڵا', 0, 16);
            $qty = $item->quantity;
            $total = number_format((float) $item->total_price, 0);

            $buffer .= sprintf("%-16s %4s %9s\n", $name, $qty, $total);
        }

        // کۆی گشتی و لێدەرکردن
        $buffer .= "--------------------------------\n";
        $buffer .= self::ESC . "a" . "\x02"; // Align Right
        $buffer .= "کۆی کاڵاکان: " . number_format((float) $order->subtotal, 0) . " د.ع\n";

        if ((float) $order->discount_amount > 0) {
            $buffer .= "داشکاندن: -" . number_format((float) $order->discount_amount, 0) . " د.ع\n";
        }

        $buffer .= self::ESC . "E" . "\x01"; // Bold
        $buffer .= "کۆی گشتی: " . number_format((float) $order->grand_total, 0) . " د.ع\n";
        $buffer .= self::ESC . "E" . "\x00";

        $buffer .= "بڕی وەرگیراو: " . number_format((float) $order->paid_amount, 0) . " د.ع\n";
        $buffer .= "بڕی گەڕاوە: " . number_format((float) $order->change_due, 0) . " د.ع\n";

        // کۆتایی و هێنانەخوارەوەی کاغەز
        $buffer .= self::ESC . "a" . "\x01"; // Align Center
        $buffer .= "سوپاس بۆ سەردانەکەتان\n\n\n";

        // فەرمانی بڕینی کاغەز (Cut Paper)
        $buffer .= self::GS . "V" . "\x41" . "\x03";

        return $buffer;
    }
}
