<?php

declare(strict_types=1);

namespace App\Domains\System\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
