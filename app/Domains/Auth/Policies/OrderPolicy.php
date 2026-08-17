<?php

declare(strict_types=1);

namespace App\Domains\Auth\Policies;

use App\Domains\Auth\Models\User;

class OrderPolicy
{
    public function view(User $user): bool
    {
        return false;
    }
}
