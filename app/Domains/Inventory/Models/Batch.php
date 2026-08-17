<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Domains\Organization\Models\Warehouse;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'stock_qty' => 'decimal:3',
        'purchase_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}