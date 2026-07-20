<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\PushNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = PushNotification::query()
            ->where('user_id', $request->user()->id)
            ->when($request->boolean('unread'), fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->limit(min((int) $request->integer('limit', 50), 100))
            ->get()
            ->map(fn (PushNotification $notification): array => NotificationResource::make($notification)->resolve($request))
            ->values();

        return response()->json([
            'data' => $notifications,
            'notifications' => $notifications,
            'unread_count' => $this->unreadCountFor($request),
            'unreadCount' => $this->unreadCountFor($request),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->unreadCountFor($request);

        return response()->json([
            'data' => ['count' => $count],
            'count' => $count,
            'unread_count' => $count,
            'unreadCount' => $count,
        ]);
    }

    public function markRead(Request $request, PushNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->forceFill([
            'status' => 'read',
            'read_at' => $notification->read_at ?? now(),
        ])->save();

        return response()->json([
            'message' => 'Notification lue.',
            'data' => NotificationResource::make($notification)->resolve($request),
            'notification' => NotificationResource::make($notification)->resolve($request),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        PushNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update([
                'status' => 'read',
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Notifications lues.',
            'unread_count' => 0,
            'unreadCount' => 0,
        ]);
    }

    private function unreadCountFor(Request $request): int
    {
        return PushNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();
    }

    private function payload(PushNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data ?? [],
            'channel' => $notification->channel,
            'status' => $notification->status,
            'sent_at' => $notification->sent_at?->toISOString(),
            'sentAt' => $notification->sent_at?->toISOString(),
            'read_at' => $notification->read_at?->toISOString(),
            'readAt' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'createdAt' => $notification->created_at?->toISOString(),
        ];
    }
}
