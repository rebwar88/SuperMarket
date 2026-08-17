<?php

declare(strict_types=1);

namespace App\Domains\Pricing\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'points' => 'integer',
    ];

    public function loyaltyAccount(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class);
    }
}