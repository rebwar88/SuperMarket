<?php

declare(strict_types=1);

namespace App\Domains\Auth\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $guard_name = 'web';
}
