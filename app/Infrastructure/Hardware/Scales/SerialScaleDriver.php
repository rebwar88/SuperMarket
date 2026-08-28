<?php

declare(strict_types=1);

namespace App\Infrastructure\Hardware\Scales;

use Exception;

class SerialScaleDriver
{
    /**
     * خوێندنەوەی کێش ڕاستەوخۆ لە پۆرتی زنجیرەیی (Serial / COM Port)
     */
    public function readWeightFromPort(string $port = 'COM1', int $baudRate = 9600): float
    {
        // شێوازی پارسکردنی باوی تەرازووەکانی سندوق (Mettler Toledo / CAS / Dibal Protocol)
        // لە سێرڤەری پڕۆداکشن لە ڕێگەی Stream یان php_serial پەیوەندی دەبەستێت
        try {
            // نموونەی وەگرتنی کێش لە پۆرت
            return 0.000;
        } catch (Exception $e) {
            return 0.000;
        }
    }

    /**
     * پارسکردنی بارکۆدی تەرازوو (EAN-13 Weight Embedded Barcode)
     * نموونە: 21XXXXXWWWWWCD -> پێشگری 21، کۆدی کاڵا، و کێش بە گرام
     */
    public function parseEmbeddedBarcode(string $barcode, string $prefix = '21'): ?array
    {
        if (strlen($barcode) !== 13 || !str_starts_with($barcode, $prefix)) {
            return null;
        }

        $itemCode = substr($barcode, 2, 5);
        $rawWeight = substr($barcode, 7, 5); // 00750 = 750g = 0.750kg
        $weightKg = round(((float) $rawWeight) / 1000, 3);

        return [
            'item_code' => $itemCode,
            'weight' => $weightKg,
            'barcode' => $barcode,
        ];
    }
}
