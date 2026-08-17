<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use UUID;

    protected $guarded = [];

    public function entryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}