<?php

declare(strict_types=1);

namespace App\Domains\POS\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Customers\Models\Customer;
use App\Domains\Organization\Models\Register;
use App\Domains\Organization\Models\Store;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Order extends Model
{
    use HasUuids;

    protected $table = 'orders';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function registerShift(): BelongsTo
    {
        return $this->belongsTo(RegisterShift::class, 'register_shift_id');
    }

    public function register(): HasOneThrough
    {
        return $this->hasOneThrough(
            Register::class,
            RegisterShift::class,
            'id',                // کلیل لە register_shifts
            'id',                // کلیل لە registers
            'register_shift_id', // کلیلی دەرەکی لە orders
            'register_id'        // کلیلی دەرەکی لە register_shifts
        );
    }
}
