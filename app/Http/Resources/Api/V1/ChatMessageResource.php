<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'scope' => $this->scope,
            'group_id' => $this->social_group_id,
            'groupId' => $this->social_group_id,
            'conversation_id' => $this->direct_conversation_id,
            'conversationId' => $this->direct_conversation_id,
            'call_id' => $this->call_session_id,
            'callId' => $this->call_session_id,
            'sender_id' => $this->sender_id,
            'senderId' => $this->sender_id,
            'sender' => $this->whenLoaded('sender', fn () => $this->sender ? new UserResource($this->sender) : null),
            'body' => $this->body,
            'message' => $this->body,
            'text' => $this->body,
            'message_type' => $this->message_type,
            'messageType' => $this->message_type,
            'type' => $this->message_type,
            'media_url' => $this->media_url,
            'mediaUrl' => $this->media_url,
            'audio_url' => $this->message_type === 'audio' ? $this->media_url : null,
            'audioUrl' => $this->message_type === 'audio' ? $this->media_url : null,
            'metadata' => $this->metadata ?? [],
            'status' => $this->status,
            'reported_at' => $this->reported_at?->toISOString(),
            'reportedAt' => $this->reported_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
