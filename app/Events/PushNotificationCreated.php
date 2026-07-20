<?php

namespace App\Events;

use App\Models\PushNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PushNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(public PushNotification $notification)
    {
        $this->payload = [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data ?? [],
            'status' => $notification->status,
            'read_at' => $notification->read_at?->toISOString(),
            'readAt' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'createdAt' => $notification->created_at?->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('users.'.$this->notification->user_id)];
    }

    public function broadcastWith(): array
    {
        return ['notification' => $this->payload];
    }
}
