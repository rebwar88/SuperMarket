<?php

declare(strict_types=1);

namespace App\Domains\Purchases\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'total_balance' => 'decimal:2',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SupplierLedger::class);
    }
}