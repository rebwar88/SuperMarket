<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Domains\POS\Models\Order;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
        'expires_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}