<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Domains\Inventory\Models\Barcode;
use App\Domains\Inventory\Models\Category;
use App\Domains\Inventory\Models\StockBatch;
use App\Domains\Inventory\Models\Unit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'unit_id',
        'retail_price',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class);
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }
}
