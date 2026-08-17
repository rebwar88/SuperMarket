<?php

declare(strict_types=1);

namespace App\Domains\Pricing\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}