<?php

declare(strict_types=1);

namespace App\Domains\Customers\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'discount_percent' => 'decimal:2',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}