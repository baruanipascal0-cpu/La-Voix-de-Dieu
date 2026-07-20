<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Social\BaseSocialController;
use App\Http\Requests\Api\V1\CreateGroupRequest;
use App\Http\Requests\Api\V1\StoreChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\SocialGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GroupController extends BaseSocialController
{
    public function index(Request $request): JsonResponse
    {
        $groups = SocialGroup::query()
            ->withCount(['members' => fn (Builder $query) => $query->where('social_group_members.status', 'active')])
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereNull('suspended_at')
            ->whereNull('blocked_at')
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (SocialGroup $group): array => $this->groupPayload($group, $request))
            ->values();

        return response()->json([
            'data' => $groups,
            'groups' => $groups,
        ]);
    }

    public function store(CreateGroupRequest $request): JsonResponse
    {
        $payload = $request->groupPayload();
        $canCreateApprovedGroup = $this->canCreateApprovedGroup($request->user());
        $status = $canCreateApprovedGroup ? 'approved' : 'pending';

        $group = SocialGroup::create([
            'name' => $payload['name'],
            'slug' => $payload['slug'] ?? $this->uniqueGroupSlug($payload['name']),
            'description' => $payload['description'],
            'avatar_url' => $payload['avatar_url'],
            'cover_url' => $payload['cover_url'],
            'type' => $payload['type'],
            'status' => $status,
            'is_public' => $canCreateApprovedGroup ? $payload['is_public'] : false,
            'requires_approval' => $canCreateApprovedGroup ? $payload['requires_approval'] : true,
            'is_active' => $canCreateApprovedGroup,
            'created_by' => $request->user()->id,
            'approved_by' => $canCreateApprovedGroup ? $request->user()->id : null,
            'approved_at' => $canCreateApprovedGroup ? now() : null,
        ]);

        $group->members()->attach($request->user()->id, [
            'role' => 'owner',
            'status' => $canCreateApprovedGroup ? 'active' : 'pending',
            'joined_at' => $canCreateApprovedGroup ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $memberIds = collect($payload['member_ids'] ?? [])
            ->map(fn (mixed $memberId): int => (int) $memberId)
            ->filter(fn (int $memberId): bool => $memberId !== $request->user()->id)
            ->unique()
            ->values();

        if ($memberIds->isNotEmpty()) {
            $memberStatus = $canCreateApprovedGroup ? 'active' : 'pending';
            $joinedAt = $canCreateApprovedGroup ? now() : null;

            $group->members()->syncWithoutDetaching(
                $memberIds
                    ->mapWithKeys(fn (int $memberId): array => [
                        $memberId => [
                            'role' => 'member',
                            'status' => $memberStatus,
                            'joined_at' => $joinedAt,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ])
                    ->all()
            );
        }

        return response()->json([
            'message' => $canCreateApprovedGroup ? 'Groupe cree.' : 'Demande de groupe envoyee.',
            'data' => $this->groupPayload($group->loadCount('members'), $request),
            'group' => $this->groupPayload($group, $request),
        ], $canCreateApprovedGroup ? 201 : 202);
    }

    public function mine(Request $request): JsonResponse
    {
        $groups = SocialGroup::query()
            ->withCount(['members' => fn (Builder $query) => $query->where('social_group_members.status', 'active')])
            ->whereNull('blocked_at')
            ->where(function (Builder $query) use ($request): void {
                $query
                    ->where('created_by', $request->user()->id)
                    ->orWhereHas('members', fn (Builder $members) => $members
                        ->where('users.id', $request->user()->id)
                        ->whereIn('social_group_members.status', ['active', 'pending']));
            })
            ->orderBy('name')
            ->get()
            ->map(fn (SocialGroup $group): array => $this->groupPayload($group, $request))
            ->values();

        return response()->json([
            'data' => $groups,
            'groups' => $groups,
            'mine' => $groups,
        ]);
    }

    public function join(Request $request, string $group): JsonResponse
    {
        $group = $this->findGroup($group);
        $membership = $group->members()
            ->where('users.id', $request->user()->id)
            ->first()?->pivot;

        if ($membership?->status === 'active') {
            return response()->json([
                'message' => 'Groupe deja rejoint.',
                'data' => $this->groupPayload($group->loadCount('members'), $request),
                'group' => $this->groupPayload($group, $request),
                'status' => 'active',
            ]);
        }

        abort_unless(
            $group->is_public || $membership || $this->canManageSocial($request->user()),
            403,
        );

        $status = $group->requires_approval && ! $this->canManageSocial($request->user()) ? 'pending' : 'active';

        $group->members()->syncWithoutDetaching([
            $request->user()->id => [
                'role' => 'member',
                'status' => $status,
                'joined_at' => $status === 'active' ? now() : null,
                'updated_at' => now(),
            ],
        ]);

        return response()->json([
            'message' => $status === 'active' ? 'Groupe rejoint.' : 'Demande d\'adhesion envoyee.',
            'data' => $this->groupPayload($group->loadCount('members'), $request),
            'group' => $this->groupPayload($group, $request),
            'status' => $status,
        ], $status === 'active' ? 200 : 202);
    }

    public function messages(Request $request, string $group): JsonResponse
    {
        $group = $this->findGroup($group);
        abort_unless($this->canAccessGroup($request->user(), $group), 403);

        $messages = $this->recentMessages(
            ChatMessage::query()
                ->where('scope', 'group')
                ->where('social_group_id', $group->id),
            $request,
        );

        return response()->json([
            'data' => $messages,
            'group' => $this->groupPayload($group, $request),
            'messages' => $messages,
        ]);
    }

    public function storeMessage(StoreChatMessageRequest $request, string $group): JsonResponse
    {
        $group = $this->findGroup($group);
        $this->ensureGroupMemberForPosting($request->user(), $group);

        $validated = $request->messagePayload();

        $message = $this->createMessage([
            'scope' => 'group',
            'social_group_id' => $group->id,
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
            'message_type' => $validated['message_type'],
            'media_url' => $validated['media_url'],
            'metadata' => $validated['metadata'],
        ]);

        return response()->json([
            'message' => 'Message au groupe envoye.',
            'data' => $this->messagePayload($message->load('sender', 'group'), $request),
            'chat_message' => $this->messagePayload($message, $request),
            'chatMessage' => $this->messagePayload($message, $request),
        ], 201);
    }

    private function canCreateApprovedGroup($user): bool
    {
        try {
            return $user->hasPermissionTo('create social groups', 'web');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    private function canManageSocial($user): bool
    {
        return $user?->can('manage social') ?? false;
    }
}
