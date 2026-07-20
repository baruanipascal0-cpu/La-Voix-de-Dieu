<?php

use App\Models\CallSession;
use App\Models\DirectConversation;
use App\Models\SocialGroup;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}', function (User $user, int $userId): bool {
    return $user->id === $userId || $user->can('manage users');
});

Broadcast::channel('groups.{groupId}', function (User $user, int $groupId): bool {
    return SocialGroup::query()
        ->whereKey($groupId)
        ->where('status', 'approved')
        ->whereNull('suspended_at')
        ->whereNull('blocked_at')
        ->where(function ($query) use ($user): void {
            $query
                ->where('is_public', true)
                ->orWhereHas('members', fn ($members) => $members
                    ->where('users.id', $user->id)
                    ->where('social_group_members.status', 'active'));
        })
        ->exists();
});

Broadcast::channel('dm.{conversationId}', function (User $user, int $conversationId): bool {
    return DirectConversation::query()
        ->whereKey($conversationId)
        ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
        ->exists();
});

Broadcast::channel('calls.{callId}', function (User $user, int $callId): bool {
    $call = CallSession::query()
        ->with('group.members')
        ->find($callId);

    if (! $call) {
        return false;
    }

    return $call->call_type === 'public'
        || $call->initiator_id === $user->id
        || $call->recipient_id === $user->id
        || $call->group?->members->contains('id', $user->id);
});
