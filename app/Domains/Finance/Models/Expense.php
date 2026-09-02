<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'category',
        'amount',
        'created_at',
        'notes',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
