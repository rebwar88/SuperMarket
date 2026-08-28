<?php

declare(strict_types=1);

namespace App\Domains\POS\Actions;

use App\Domains\POS\DTOs\ShiftData;
use App\Domains\POS\Models\Order;
use App\Domains\POS\Models\RegisterShift;
use Exception;

class CloseShiftAction
{
    public function execute(string $shiftId, ShiftData $data): RegisterShift
    {
        $shift = RegisterShift::findOrFail($shiftId);

        if ($shift->status !== 'open') {
            throw new Exception("ئەم شیفتە پێشتر داخراوە.");
        }

        // حیسابکردنی کۆی فرۆشتنی کاش لەم شیفتەدا
        $totalSalesCash = (float) Order::where('register_shift_id', $shift->id)
            ->where('status', 'completed')
            ->sum('grand_total');

        $expectedCash = (float) $shift->opening_cash + $totalSalesCash;
        $closingCash = (float) ($data->closingCash ?? 0.00);
        $cashDifference = $closingCash - $expectedCash;

        $shift->update([
            'closing_cash' => $closingCash,
            'cash_difference' => $cashDifference,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return $shift;
    }
}
