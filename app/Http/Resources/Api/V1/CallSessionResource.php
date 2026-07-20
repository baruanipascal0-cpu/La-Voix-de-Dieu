<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'call_id' => $this->id,
            'callId' => $this->id,
            'call_type' => $this->call_type,
            'callType' => $this->call_type,
            'status' => $this->status,
            'title' => $this->title,
            'provider' => $this->provider,
            'room_name' => $this->room_name,
            'roomName' => $this->room_name,
            'channel_name' => $this->channel_name,
            'channelName' => $this->channel_name,
            'token' => null,
            'initiator_id' => $this->initiator_id,
            'initiatorId' => $this->initiator_id,
            'recipient_id' => $this->recipient_id,
            'recipientId' => $this->recipient_id,
            'initiator' => $this->whenLoaded('initiator', fn () => $this->initiator ? new UserResource($this->initiator) : null),
            'recipient' => $this->whenLoaded('recipient', fn () => $this->recipient ? new UserResource($this->recipient) : null),
            'group' => $this->whenLoaded('group', fn () => $this->group ? new SocialGroupResource($this->group) : null),
            'started_at' => $this->started_at?->toISOString(),
            'startedAt' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'endedAt' => $this->ended_at?->toISOString(),
            'last_state_at' => $this->last_state_at?->toISOString(),
            'lastStateAt' => $this->last_state_at?->toISOString(),
            'metadata' => $this->metadata ?? [],
            'created_at' => $this->created_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
