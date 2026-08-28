<?php

declare(strict_types=1);

namespace App\Domains\POS\Models;

use App\Domains\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type', // percentage, fixed_discount, bogo
        'product_id',
        'discount_value',
        'buy_quantity',
        'get_quantity',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
