<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }
}
