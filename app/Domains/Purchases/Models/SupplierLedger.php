<?php

declare(strict_types=1);

namespace App\Domains\Purchases\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierLedger extends Model
{
    use UUID;

    protected $table = 'supplier_ledger';
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}