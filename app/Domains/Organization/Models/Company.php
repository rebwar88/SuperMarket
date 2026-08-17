<?php

declare(strict_types=1);

namespace App\Domains\Organization\Models;

use App\Support\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use UUID;

    protected $guarded = [];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }
}