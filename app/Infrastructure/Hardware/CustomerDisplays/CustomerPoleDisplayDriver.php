<?php

declare(strict_types=1);

namespace App\Infrastructure\Hardware\CustomerDisplays;

class CustomerPoleDisplayDriver
{
    private const ESC = "\x1b";
    private const CLR = "\x0c";

    /**
     * پیشاندانی ناو و نرخی کاڵا لەسەر شاشەی دوو دێڕی کڕیار
     */
    public function showItem(string $itemName, float $price): string
    {
        $line1 = mb_str_pad(mb_substr($itemName, 0, 20), 20);
        $line2 = mb_str_pad("IQD " . number_format($price, 0), 20, " ", STR_PAD_LEFT);

        return self::CLR . $line1 . $line2;
    }

    /**
     * پیشاندانی کۆی گشتی و پەیامی سوپاسگوزاری لە کاتی تەواوبوونی کڕین
     */
    public function showTotal(float $grandTotal): string
    {
        $line1 = mb_str_pad("TOTAL AMOUNT:", 20);
        $line2 = mb_str_pad("IQD " . number_format($grandTotal, 0), 20, " ", STR_PAD_LEFT);

        return self::CLR . $line1 . $line2;
    }

    public function clear(): string
    {
        return self::CLR;
    }
}
