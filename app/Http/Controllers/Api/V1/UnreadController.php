<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Social\BaseSocialController;
use App\Models\CallSession;
use App\Models\ChatMessage;
use App\Models\ChatReadReceipt;
use App\Models\DirectConversation;
use App\Models\SocialGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnreadController extends BaseSocialController
{
    public function social(Request $request): JsonResponse
    {
        $user = $request->user();

        $publicReceipt = ChatReadReceipt::query()
            ->where('user_id', $user->id)
            ->where('scope', 'public')
            ->first();

        $publicUnread = ChatMessage::query()
            ->where('scope', 'public')
            ->where('sender_id', '!=', $user->id)
            ->where('status', 'published')
            ->when($publicReceipt?->last_seen_at, fn (Builder $query, $date) => $query->where('created_at', '>', $date))
            ->count();

        $dmUnread = $user->directConversations()
            ->get()
            ->sum(fn (DirectConversation $conversation): int => $this->unreadForConversation($conversation, $user));

        $groupUnread = $user->socialGroups()
            ->wherePivot('status', 'active')
            ->where('social_groups.status', 'approved')
            ->get()
            ->sum(fn (SocialGroup $group): int => $this->unreadForGroup($group, $user));

        $callUnread = CallSession::query()
            ->where('recipient_id', $user->id)
            ->where('status', 'ringing')
            ->count();

        $payload = [
            'public_chat' => $publicUnread,
            'publicChat' => $publicUnread,
            'dm' => $dmUnread,
            'groups' => $groupUnread,
            'calls' => $callUnread,
            'total' => $publicUnread + $dmUnread + $groupUnread + $callUnread,
        ];

        return response()->json([
            'data' => $payload,
            'unread' => $payload,
        ]);
    }
}
