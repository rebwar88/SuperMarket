<?php

declare(strict_types=1);

namespace App\Domains\CRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyPayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'party_id',
        'amount',
        'payment_type', // receipt (وەرگرتنەوە), payout (پێدان بە دابینکەر)
        'payment_method',
        'notes',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }
}
