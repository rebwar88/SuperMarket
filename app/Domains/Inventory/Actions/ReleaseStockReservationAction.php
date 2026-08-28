<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\StockReservation;

class ReleaseStockReservationAction
{
    public function execute(string $reservationId): bool
    {
        $reservation = StockReservation::find($reservationId);

        if ($reservation) {
            return (bool) $reservation->delete();
        }

        return false;
    }
}