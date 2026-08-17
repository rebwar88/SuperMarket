<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckExpiringBatchesJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
        //
    }
}
