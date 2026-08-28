<?php

declare(strict_types=1);

namespace App\Domains\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Register extends Model
{
    protected $guarded = [];

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
