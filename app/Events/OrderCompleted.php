<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OrderCompleted
{
    use Dispatchable;

    public function __construct()
    {
    }
}
