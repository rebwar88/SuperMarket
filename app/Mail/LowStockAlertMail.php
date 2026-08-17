<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;

class LowStockAlertMail extends Mailable
{
    public function build()
    {
        return $this;
    }
}
