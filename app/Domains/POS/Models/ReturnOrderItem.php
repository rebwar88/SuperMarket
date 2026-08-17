<?php

declare(strict_types=1);

namespace App\Domains\POS\Models;

use App\Domains\Inventory\Models\Product;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnOrderItem extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
        'refund_price' => 'decimal:2',
    ];

    public function returnOrder(): BelongsTo
    {
        return $this->belongsTo(ReturnOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}