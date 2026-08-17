<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Domains\Organization\Models\Warehouse;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}