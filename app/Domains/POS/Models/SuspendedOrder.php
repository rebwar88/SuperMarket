<?php

declare(strict_types=1);

namespace App\Domains\POS\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Organization\Models\Register;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspendedOrder extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'cart_data' => 'array',
        'parked_at' => 'datetime',
    ];

    public function register(): BelongsTo
    {
        return $this->belongsTo(Register::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}