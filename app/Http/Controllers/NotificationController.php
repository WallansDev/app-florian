<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = AppNotification::where('user_id', $request->user('api')->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = AppNotification::where('user_id', $request->user('api')->id)
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        AppNotification::where('user_id', $request->user('api')->id)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Toutes les notifications ont été lues.']);
    }
}
