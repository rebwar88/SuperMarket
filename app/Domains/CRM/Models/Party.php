<?php

declare(strict_types=1);

namespace App\Domains\CRM\Models;

use App\Domains\Finance\Models\Account;
use App\Domains\POS\Models\Order;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'phone',
        'type', // customer, supplier, both
        'credit_limit',
        'current_balance', // پۆزەتیڤ = پارەمان لایەتی، نێگەتیڤ = پارەمان لایەتی/دابینکەر
        'account_id',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PartyPayment::class);
    }
}
