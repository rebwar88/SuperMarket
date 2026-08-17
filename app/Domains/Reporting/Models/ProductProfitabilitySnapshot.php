<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

use App\Domains\Inventory\Models\Product;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProfitabilitySnapshot extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'snapshot_date' => 'date',
        'margin_percent' => 'decimal:2',
        'units_sold' => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}