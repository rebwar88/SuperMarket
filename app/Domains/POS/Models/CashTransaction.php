<?php

declare(strict_types=1);

namespace App\Domains\POS\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function registerShift(): BelongsTo
    {
        return $this->belongsTo(RegisterShift::class);
    }
}