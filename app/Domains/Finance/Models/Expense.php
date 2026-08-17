<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Domains\Organization\Models\Store;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}