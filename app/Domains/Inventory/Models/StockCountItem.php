<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'counted_qty' => 'decimal:3',
        'system_qty' => 'decimal:3',
    ];

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}