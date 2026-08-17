<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barcode extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'packing_qty' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}