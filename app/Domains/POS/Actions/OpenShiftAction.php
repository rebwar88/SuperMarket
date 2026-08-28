<?php

declare(strict_types=1);

namespace App\Domains\POS\Actions;

use App\Domains\POS\DTOs\ShiftData;
use App\Domains\POS\Models\RegisterShift;
use Exception;

class OpenShiftAction
{
    public function execute(ShiftData $data): RegisterShift
    {
        // دڵنیابوونەوە لەوەی سندوقەکە شیفتی کراوەی پێشووتری نییە
        $existingOpenShift = RegisterShift::where('register_id', $data->registerId)
            ->where('status', 'open')
            ->first();

        if ($existingOpenShift) {
            throw new Exception("ئەم سندوقە پێشتر شیفتێکی کراوەی هەیە.");
        }

        return RegisterShift::create([
            'register_id' => $data->registerId,
            'user_id' => $data->userId,
            'opening_cash' => $data->openingCash,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }
}
