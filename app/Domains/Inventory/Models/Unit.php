<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'allow_decimal' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}