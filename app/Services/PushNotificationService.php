<?php

namespace App\Services;

use App\Events\PushNotificationCreated;
use App\Models\CallSession;
use App\Models\ChatMessage;
use App\Models\PushNotification;
use App\Models\User;

class PushNotificationService
{
    public function notifyUser(User $user, string $type, string $title, ?string $body = null, array $data = [], ?User $actor = null): PushNotification
    {
        $notification = PushNotification::create([
            'user_id' => $user->id,
            'actor_id' => $actor?->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'channel' => 'fcm',
            'status' => 'queued',
        ]);

        app(FirebasePushService::class)->send($notification);

        event(new PushNotificationCreated($notification));

        return $notification;
    }

    public function notifyUsers(iterable $users, string $type, string $title, ?string $body = null, array $data = [], ?User $actor = null): void
    {
        foreach ($users as $user) {
            if ($user instanceof User && $user->is_active) {
                $this->notifyUser($user, $type, $title, $body, $data, $actor);
            }
        }
    }

    public function notifyChatMessage(ChatMessage $message): void
    {
        $message->loadMissing(['sender', 'conversation.participants', 'group.members']);

        if (! $message->sender) {
            return;
        }

        if ($message->scope === 'dm' && $message->conversation) {
            $this->notifyUsers(
                $message->conversation->participants->where('id', '!=', $message->sender_id),
                'chat.dm',
                $message->sender->name,
                $this->notificationBody($message),
                [
                    'screen' => 'dm',
                    'conversation_id' => $message->direct_conversation_id,
                    'conversationId' => $message->direct_conversation_id,
                    'message_id' => $message->id,
                    'messageId' => $message->id,
                ],
                $message->sender,
            );

            return;
        }

        if ($message->scope === 'group' && $message->group) {
            $members = $message->group->members()
                ->where('users.id', '!=', $message->sender_id)
                ->wherePivot('status', 'active')
                ->get();

            $this->notifyUsers(
                $members,
                'chat.group',
                $message->group->name,
                $message->sender->name.': '.$this->notificationBody($message),
                [
                    'screen' => 'group',
                    'group_id' => $message->social_group_id,
                    'groupId' => $message->social_group_id,
                    'message_id' => $message->id,
                    'messageId' => $message->id,
                ],
                $message->sender,
            );
        }
    }

    public function notifyCallSession(CallSession $call): void
    {
        $call->loadMissing(['initiator', 'recipient', 'group.members']);

        if (! $call->initiator || $call->status !== 'ringing') {
            return;
        }

        $data = [
            'screen' => 'call',
            'call_id' => $call->id,
            'callId' => $call->id,
            'uuid' => $call->uuid,
            'call_type' => $call->call_type,
            'callType' => $call->call_type,
            'room_name' => $call->room_name,
            'roomName' => $call->room_name,
        ];

        if ($call->recipient) {
            $this->notifyUser(
                $call->recipient,
                'call.ringing',
                'Appel entrant',
                $call->initiator->name.' vous appelle',
                $data,
                $call->initiator,
            );

            return;
        }

        if ($call->group) {
            $this->notifyUsers(
                $call->group->members->where('id', '!=', $call->initiator_id),
                'call.group',
                $call->group->name,
                $call->initiator->name.' a lance un appel',
                $data + ['group_id' => $call->social_group_id, 'groupId' => $call->social_group_id],
                $call->initiator,
            );
        }
    }

    private function notificationBody(ChatMessage $message): string
    {
        if (filled($message->body)) {
            return str($message->body)->limit(140)->toString();
        }

        return match ($message->message_type) {
            'audio' => 'Message audio',
            'image' => 'Image',
            'video' => 'Video',
            default => 'Nouveau message',
        };
    }
}
