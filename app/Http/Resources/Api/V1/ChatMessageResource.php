<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $metadata = $this->metadata ?? [];
        $isMine = $this->sender_id === $request->user()?->id;
        $seen = $this->seenByRecipients($request);

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
            'media_id' => $metadata['media_id'] ?? null,
            'mediaId' => $metadata['media_id'] ?? null,
            'duration' => $metadata['duration'] ?? null,
            'mime_type' => $metadata['mime_type'] ?? null,
            'mimeType' => $metadata['mime_type'] ?? null,
            'file_name' => $metadata['file_name'] ?? null,
            'fileName' => $metadata['file_name'] ?? null,
            'metadata' => $this->metadata ?? [],
            'is_mine' => $isMine,
            'isMine' => $isMine,
            'seen' => $seen,
            'is_seen' => $seen,
            'isSeen' => $seen,
            'status' => $this->status,
            'reported_at' => $this->reported_at?->toISOString(),
            'reportedAt' => $this->reported_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }

    private function seenByRecipients(Request $request): bool
    {
        $user = $request->user();

        if (! $user || $this->sender_id !== $user->id || $this->scope !== 'dm' || ! $this->created_at) {
            return false;
        }

        if (! $this->relationLoaded('conversation') || ! $this->conversation?->relationLoaded('participants')) {
            return false;
        }

        return $this->conversation->participants
            ->where('id', '!=', $user->id)
            ->contains(function ($participant): bool {
                $readAt = $participant->pivot?->last_read_at;

                return filled($readAt)
                    && Carbon::parse($readAt)->greaterThanOrEqualTo($this->created_at);
            });
    }
}
