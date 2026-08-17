<?php

declare(strict_types=1);

namespace App\Domains\Auth\Models;

use App\Domains\Organization\Models\Store;
use App\Support\UUID;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, UUID;

    protected $guard_name = 'web';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_users')
            ->withPivot('role')
            ->withTimestamps();
    }
}