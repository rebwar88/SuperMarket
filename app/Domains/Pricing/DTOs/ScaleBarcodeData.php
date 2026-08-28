<?php

declare(strict_types=1);

namespace App\Domains\Pricing\DTOs;

final class ScaleBarcodeData
{
    public function __construct(
        public readonly string $prefix,
        public readonly string $itemCode,
        public readonly float $weightInKg,
        public readonly string $checksum
    ) {}
}
