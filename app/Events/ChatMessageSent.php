<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(public ChatMessage $message)
    {
        $message->loadMissing(['sender', 'group', 'conversation']);
        $this->payload = $this->messagePayload($message);
    }

    public function broadcastAs(): string
    {
        return 'chat.message.sent';
    }

    public function broadcastOn(): array
    {
        return match ($this->message->scope) {
            'group' => [new PrivateChannel('groups.'.$this->message->social_group_id)],
            'dm' => [new PrivateChannel('dm.'.$this->message->direct_conversation_id)],
            'call' => [new PrivateChannel('calls.'.$this->message->call_session_id)],
            default => [new Channel('public.chat')],
        };
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->payload];
    }

    private function messagePayload(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'uuid' => $message->uuid,
            'scope' => $message->scope,
            'group_id' => $message->social_group_id,
            'groupId' => $message->social_group_id,
            'conversation_id' => $message->direct_conversation_id,
            'conversationId' => $message->direct_conversation_id,
            'call_id' => $message->call_session_id,
            'callId' => $message->call_session_id,
            'sender_id' => $message->sender_id,
            'senderId' => $message->sender_id,
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'avatar_url' => $message->sender->avatar_url,
                'avatarUrl' => $message->sender->avatar_url,
            ] : null,
            'body' => $message->body,
            'message' => $message->body,
            'text' => $message->body,
            'message_type' => $message->message_type,
            'messageType' => $message->message_type,
            'type' => $message->message_type,
            'media_url' => $message->media_url,
            'mediaUrl' => $message->media_url,
            'metadata' => $message->metadata ?? [],
            'created_at' => $message->created_at?->toISOString(),
            'createdAt' => $message->created_at?->toISOString(),
        ];
    }
}
