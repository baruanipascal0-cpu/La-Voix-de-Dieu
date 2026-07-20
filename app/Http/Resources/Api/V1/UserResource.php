<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canSeePrivate = $request->user()?->id === $this->id || $request->user()?->can('manage users');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar_url,
            'avatar_url' => $this->avatar_url,
            'avatarUrl' => $this->avatar_url,
            'photo_url' => $this->avatar_url,
            'photoUrl' => $this->avatar_url,
            'role' => $this->role,
            'roles' => $this->when($canSeePrivate, fn () => $this->getRoleNames()->values()),
            'permissions' => $this->when($canSeePrivate, fn () => $this->getAllPermissions()->pluck('name')->values()),
            'email' => $this->when($canSeePrivate, $this->email),
            'phone' => $this->when($canSeePrivate, $this->phone),
            'is_active' => $this->when($canSeePrivate, $this->is_active),
            'isActive' => $this->when($canSeePrivate, $this->is_active),
            'email_verified_at' => $this->when($canSeePrivate, $this->email_verified_at?->toISOString()),
            'emailVerifiedAt' => $this->when($canSeePrivate, $this->email_verified_at?->toISOString()),
            'last_seen_at' => $this->when($canSeePrivate, $this->last_seen_at?->toISOString()),
            'lastSeenAt' => $this->when($canSeePrivate, $this->last_seen_at?->toISOString()),
            'suspended_at' => $this->when($canSeePrivate, $this->suspended_at?->toISOString()),
            'suspendedAt' => $this->when($canSeePrivate, $this->suspended_at?->toISOString()),
            'blocked_at' => $this->when($canSeePrivate, $this->blocked_at?->toISOString()),
            'blockedAt' => $this->when($canSeePrivate, $this->blocked_at?->toISOString()),
            'created_at' => $this->when($canSeePrivate, $this->created_at?->toISOString()),
            'createdAt' => $this->when($canSeePrivate, $this->created_at?->toISOString()),
        ];
    }
}
