<?php

namespace App\Events;

use App\Models\CallSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSessionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(public CallSession $call)
    {
        $call->loadMissing(['initiator', 'recipient', 'group']);
        $this->payload = $this->callPayload($call);
    }

    public function broadcastAs(): string
    {
        return 'call.session.updated';
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('calls.'.$this->call->id)];

        if ($this->call->initiator_id) {
            $channels[] = new PrivateChannel('users.'.$this->call->initiator_id);
        }

        if ($this->call->recipient_id) {
            $channels[] = new PrivateChannel('users.'.$this->call->recipient_id);
        }

        if ($this->call->social_group_id) {
            $channels[] = new PrivateChannel('groups.'.$this->call->social_group_id);
        }

        if ($this->call->call_type === 'public') {
            $channels[] = new Channel('public.calls');
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return ['call' => $this->payload];
    }

    private function callPayload(CallSession $call): array
    {
        return [
            'id' => $call->id,
            'uuid' => $call->uuid,
            'call_type' => $call->call_type,
            'callType' => $call->call_type,
            'status' => $call->status,
            'title' => $call->title,
            'provider' => $call->provider,
            'room_name' => $call->room_name,
            'roomName' => $call->room_name,
            'initiator_id' => $call->initiator_id,
            'initiatorId' => $call->initiator_id,
            'recipient_id' => $call->recipient_id,
            'recipientId' => $call->recipient_id,
            'group_id' => $call->social_group_id,
            'groupId' => $call->social_group_id,
            'started_at' => $call->started_at?->toISOString(),
            'startedAt' => $call->started_at?->toISOString(),
            'ended_at' => $call->ended_at?->toISOString(),
            'endedAt' => $call->ended_at?->toISOString(),
            'last_state_at' => $call->last_state_at?->toISOString(),
            'lastStateAt' => $call->last_state_at?->toISOString(),
            'metadata' => $call->metadata ?? [],
            'created_at' => $call->created_at?->toISOString(),
            'createdAt' => $call->created_at?->toISOString(),
        ];
    }
}
