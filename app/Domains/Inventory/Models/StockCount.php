<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Domains\Organization\Models\Warehouse;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCount extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'counted_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }
}