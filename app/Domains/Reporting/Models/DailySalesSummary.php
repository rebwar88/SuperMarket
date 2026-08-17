<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

use App\Domains\Organization\Models\Store;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySalesSummary extends Model
{
    use UUID;

    protected $guarded = [];

    protected $casts = [
        'report_date' => 'date',
        'total_sales' => 'decimal:2',
        'total_gross_profit' => 'decimal:2',
        'total_transactions' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}