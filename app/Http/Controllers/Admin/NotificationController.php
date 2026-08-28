<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\System\Models\SystemNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function poll(Request $request): JsonResponse
    {
        $lastId = (int) $request->get('last_id', 0);

        $notifications = SystemNotification::latest()
            ->take(10)
            ->get();

        $newNotifications = SystemNotification::where('id', '>', $lastId)
            ->latest()
            ->get();

        $unreadCount = SystemNotification::where('is_read', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'new' => $newNotifications,
            'unread_count' => $unreadCount,
            'max_id' => SystemNotification::max('id') ?? 0,
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        SystemNotification::where('is_read', false)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }
}
