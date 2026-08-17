<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_date' => 'date',
    ];
}