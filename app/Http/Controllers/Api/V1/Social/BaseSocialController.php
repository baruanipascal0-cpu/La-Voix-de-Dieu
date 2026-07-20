<?php

namespace App\Http\Controllers\Api\V1\Social;

use App\Events\CallSessionUpdated;
use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CallSessionResource;
use App\Http\Resources\Api\V1\ChatMessageResource;
use App\Http\Resources\Api\V1\SocialGroupResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\CallSession;
use App\Models\CallSignal;
use App\Models\ChatMessage;
use App\Models\ChatReadReceipt;
use App\Models\DirectConversation;
use App\Models\DirectParticipant;
use App\Models\SocialGroup;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseSocialController extends Controller
{
    protected function createMessage(array $attributes): ChatMessage
    {
        $message = ChatMessage::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'metadata' => [],
            'status' => 'published',
        ], $attributes));

        event(new ChatMessageSent($message));
        app(PushNotificationService::class)->notifyChatMessage($message);

        return $message;
    }

    protected function recentMessages(Builder $query, Request $request): Collection
    {
        $limit = min((int) $request->integer('limit', 50), 100);

        return $query
            ->with(['sender', 'group'])
            ->where('status', 'published')
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn (ChatMessage $message): array => $this->messagePayload($message, $request))
            ->values();
    }

    protected function findGroup(string $group): SocialGroup
    {
        return SocialGroup::query()
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereNull('suspended_at')
            ->whereNull('blocked_at')
            ->where(function (Builder $query) use ($group): void {
                $query->where('slug', $group);

                if (ctype_digit($group)) {
                    $query->orWhere('id', (int) $group);
                }
            })
            ->firstOrFail();
    }

    protected function canAccessGroup(?User $user, SocialGroup $group): bool
    {
        if ($group->status !== 'approved' || $group->blocked_at || $group->suspended_at) {
            return false;
        }

        if ($group->is_public) {
            return true;
        }

        return $user && $group->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    protected function ensureGroupMemberForPosting(User $user, SocialGroup $group): void
    {
        $isMember = $group->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();

        if ($isMember) {
            return;
        }

        abort_unless($group->is_public && ! $group->requires_approval && $group->status === 'approved', 403);

        $group->members()->syncWithoutDetaching([
            $user->id => [
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    protected function resolveConversationForMessage(Request $request): DirectConversation
    {
        if ($request->filled('conversation_id') || $request->filled('conversationId') || $request->filled('uuid')) {
            return $this->findConversationForUser(
                (string) ($request->input('conversation_id') ?? $request->input('conversationId') ?? $request->input('uuid')),
                $request->user(),
            );
        }

        return $this->findOrCreateDirectConversation(
            $request->user(),
            (int) ($request->input('recipient_id') ?? $request->input('recipientId') ?? $request->input('member_id') ?? $request->input('memberId')),
        );
    }

    protected function storeDirectMessage(Request $request, DirectConversation $conversation): array
    {
        $validated = $request->messagePayload();

        $message = $this->createMessage([
            'scope' => 'dm',
            'direct_conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
            'message_type' => $validated['message_type'],
            'media_url' => $validated['media_url'],
            'metadata' => $validated['metadata'],
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();
        $conversation->participants()->updateExistingPivot($request->user()->id, ['last_read_at' => now()]);
        DirectParticipant::query()
            ->where('direct_conversation_id', $conversation->id)
            ->where('user_id', '!=', $request->user()->id)
            ->update(['last_read_at' => null]);

        return [
            'message' => $message,
            'conversation' => $conversation->fresh('participants'),
        ];
    }

    protected function findOrCreateDirectConversation(User $user, int $recipientId): DirectConversation
    {
        abort_if($recipientId === $user->id, 422, 'Le destinataire doit etre different.');

        $recipient = User::query()
            ->whereKey($recipientId)
            ->where('is_active', true)
            ->whereNull('suspended_at')
            ->whereNull('blocked_at')
            ->firstOrFail();

        $conversation = DirectConversation::query()
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $user->id))
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $recipient->id))
            ->withCount('participants')
            ->get()
            ->first(fn (DirectConversation $conversation): bool => $conversation->participants_count === 2);

        if ($conversation) {
            return $conversation;
        }

        $conversation = DirectConversation::create([
            'uuid' => (string) Str::uuid(),
            'created_by' => $user->id,
        ]);

        $conversation->participants()->attach([
            $user->id => ['created_at' => now(), 'updated_at' => now()],
            $recipient->id => ['created_at' => now(), 'updated_at' => now()],
        ]);

        return $conversation;
    }

    protected function findConversationForUser(string $conversation, User $user): DirectConversation
    {
        return DirectConversation::query()
            ->where(function (Builder $query) use ($conversation): void {
                $query->where('uuid', $conversation);

                if (ctype_digit($conversation)) {
                    $query->orWhere('id', (int) $conversation);
                }
            })
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $user->id))
            ->firstOrFail();
    }

    protected function createCallSession(Request $request, string $defaultType): CallSession
    {
        $validated = $request->validated();
        $callType = in_array($defaultType, ['dm', 'group', 'public'], true) ? $defaultType : 'dm';
        $recipientId = $validated['recipient_id'] ?? $validated['recipientId'] ?? null;
        $groupId = $validated['group_id'] ?? $validated['groupId'] ?? null;
        $conversation = null;
        $group = null;

        if ($recipientId) {
            $callType = 'dm';
            $conversation = $this->findOrCreateDirectConversation($request->user(), (int) $recipientId);
        }

        if ($groupId) {
            $group = $this->findGroup((string) $groupId);
            $this->ensureGroupMemberForPosting($request->user(), $group);
            $callType = 'group';
        }

        $uuid = (string) Str::uuid();

        $call = CallSession::create([
            'uuid' => $uuid,
            'call_type' => $callType,
            'status' => $validated['status'] ?? 'ringing',
            'title' => $validated['title'] ?? null,
            'initiator_id' => $request->user()->id,
            'recipient_id' => $recipientId,
            'social_group_id' => $group?->id,
            'direct_conversation_id' => $conversation?->id,
            'provider' => 'livekit',
            'room_name' => $validated['room_name'] ?? $validated['roomName'] ?? 'lvd-call-'.$uuid,
            'channel_name' => $validated['channel_name'] ?? $validated['channelName'] ?? null,
            'started_at' => ($validated['status'] ?? null) === 'active' ? now() : null,
            'last_state_at' => now(),
            'metadata' => $validated['metadata'] ?? [],
        ]);

        $call = $call->load(['initiator', 'recipient', 'group']);

        event(new CallSessionUpdated($call));
        app(PushNotificationService::class)->notifyCallSession($call);

        return $call;
    }

    protected function findCallForUser(Request $request, User $user): CallSession
    {
        $callId = (string) ($request->input('call_id') ?? $request->input('callId') ?? $request->input('uuid') ?? '');
        abort_if(blank($callId), 422, 'call_id est requis.');

        $call = CallSession::query()
            ->where(function (Builder $query) use ($callId): void {
                $query->where('uuid', $callId);

                if (ctype_digit($callId)) {
                    $query->orWhere('id', (int) $callId);
                }
            })
            ->with(['initiator', 'recipient', 'group.members'])
            ->firstOrFail();

        $isAllowed = $call->initiator_id === $user->id
            || $call->recipient_id === $user->id
            || $call->group?->members->contains('id', $user->id);

        abort_unless($isAllowed, 403);

        return $call;
    }

    protected function unreadForConversation(DirectConversation $conversation, User $user): int
    {
        $participant = DirectParticipant::query()
            ->where('direct_conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        return ChatMessage::query()
            ->where('scope', 'dm')
            ->where('direct_conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('status', 'published')
            ->when($participant?->last_read_at, fn (Builder $query, $date) => $query->where('created_at', '>', $date))
            ->count();
    }

    protected function unreadForGroup(SocialGroup $group, User $user): int
    {
        $lastReadAt = $group->pivot?->last_read_at;

        return ChatMessage::query()
            ->where('scope', 'group')
            ->where('social_group_id', $group->id)
            ->where('sender_id', '!=', $user->id)
            ->where('status', 'published')
            ->when($lastReadAt, fn (Builder $query, $date) => $query->where('created_at', '>', $date))
            ->count();
    }

    protected function uniqueGroupSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'groupe';
        $slug = $base;
        $index = 2;

        while (SocialGroup::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$index;
            $index++;
        }

        return $slug;
    }

    protected function normalizePayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : ['value' => $payload];
        }

        return [];
    }

    protected function messagePayload(ChatMessage $message, ?Request $request = null): array
    {
        $message->loadMissing('sender');

        return $this->resolveResource(new ChatMessageResource($message), $request);
    }

    protected function groupPayload(SocialGroup $group, ?Request $request = null): array
    {
        return $this->resolveResource(new SocialGroupResource($group), $request);
    }

    protected function callPayload(CallSession $call, ?Request $request = null): array
    {
        $call->loadMissing(['initiator', 'recipient', 'group']);

        return $this->resolveResource(new CallSessionResource($call), $request);
    }

    protected function signalPayload(CallSignal $signal): array
    {
        return [
            'id' => $signal->id,
            'call_id' => $signal->call_session_id,
            'callId' => $signal->call_session_id,
            'sender_id' => $signal->sender_id,
            'senderId' => $signal->sender_id,
            'recipient_id' => $signal->recipient_id,
            'recipientId' => $signal->recipient_id,
            'signal_type' => $signal->signal_type,
            'signalType' => $signal->signal_type,
            'type' => $signal->signal_type,
            'payload' => $signal->payload ?? [],
            'created_at' => $signal->created_at?->toISOString(),
            'createdAt' => $signal->created_at?->toISOString(),
        ];
    }

    protected function receiptPayload(ChatReadReceipt $receipt): array
    {
        return [
            'id' => $receipt->id,
            'scope' => $receipt->scope,
            'last_seen_message_id' => $receipt->last_seen_message_id,
            'lastSeenMessageId' => $receipt->last_seen_message_id,
            'last_seen_at' => $receipt->last_seen_at?->toISOString(),
            'lastSeenAt' => $receipt->last_seen_at?->toISOString(),
        ];
    }

    protected function conversationPayload(DirectConversation $conversation, User $user, ?Collection $messages = null, ?Request $request = null): array
    {
        $conversation->loadMissing('participants');
        $lastMessage = $conversation->messages()
            ->with('sender')
            ->where('status', 'published')
            ->latest()
            ->first();

        return [
            'id' => $conversation->id,
            'uuid' => $conversation->uuid,
            'subject' => $conversation->subject,
            'participants' => $conversation->participants
                ->map(fn (User $participant): array => $this->resolveResource(new UserResource($participant), $request))
                ->values(),
            'last_message' => $lastMessage ? $this->messagePayload($lastMessage, $request) : null,
            'lastMessage' => $lastMessage ? $this->messagePayload($lastMessage, $request) : null,
            'last_message_at' => $conversation->last_message_at?->toISOString(),
            'lastMessageAt' => $conversation->last_message_at?->toISOString(),
            'unread' => $this->unreadForConversation($conversation, $user),
            'messages' => $messages,
        ];
    }

    private function resolveResource(JsonResource $resource, ?Request $request = null): array
    {
        return $resource->resolve($request ?? request());
    }
}
