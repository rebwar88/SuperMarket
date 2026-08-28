<?php

declare(strict_types=1);

namespace App\Domains\Pricing\Services;

use App\Domains\Pricing\DTOs\ScaleBarcodeData;
use InvalidArgumentException;

class ScaleBarcodeParser
{
    /**
     * شیکردنەوەی بارکۆدی تەرازوو بە فۆرماتی 13 ژمارەیی:
     * نموونە: 2100045014503
     * 21 = پاشگری تەرازوو (Prefix)
     * 00045 = کۆدی کاڵا (Item Code)
     * 01450 = کێش (1450 گرام = 1.450 کیلۆگرام)
     * 3 = کۆدی پشکنین (Checksum)
     */
    public function parse(string $barcode, string $scalePrefix = '21'): ScaleBarcodeData
    {
        $barcode = trim($barcode);

        if (strlen($barcode) !== 13 || !str_starts_with($barcode, $scalePrefix)) {
            throw new InvalidArgumentException("بارکۆدی تەرازوو نادروستە یان ناگونجێت لەگەڵ پێشگری دیاریکراو.");
        }

        $prefix = substr($barcode, 0, 2);
        $itemCode = substr($barcode, 2, 5);
        $weightRaw = substr($barcode, 7, 5);
        $checksum = substr($barcode, 12, 1);

        $weightInKg = round(((int) $weightRaw) / 1000, 3);

        return new ScaleBarcodeData(
            prefix: $prefix,
            itemCode: $itemCode,
            weightInKg: $weightInKg,
            checksum: $checksum
        );
    }
}
