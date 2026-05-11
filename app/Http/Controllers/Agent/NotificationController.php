<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List unread notifications (used by topbar dropdown via AJAX).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'type', 'title', 'body', 'action_url', 'is_read', 'created_at']);

        $unreadCount = $request->user()->notifications()->unread()->count();

        return response()->json([
            'data'         => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->notifications()
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
