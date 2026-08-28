<?php

declare(strict_types=1);

namespace App\Domains\Payments\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
    ];
}
