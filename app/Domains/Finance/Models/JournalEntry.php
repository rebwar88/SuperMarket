<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
