<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isMember = $user ? $this->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists() : false;

        $membersCount = $this->members_count ?? $this->members()
            ->wherePivot('status', 'active')
            ->count();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'avatar_url' => $this->avatar_url,
            'avatarUrl' => $this->avatar_url,
            'cover_url' => $this->cover_url,
            'coverUrl' => $this->cover_url,
            'type' => $this->type,
            'status' => $this->status,
            'is_public' => $this->is_public,
            'isPublic' => $this->is_public,
            'requires_approval' => $this->requires_approval,
            'requiresApproval' => $this->requires_approval,
            'is_member' => $isMember,
            'isMember' => $isMember,
            'members_count' => $membersCount,
            'membersCount' => $membersCount,
            'created_at' => $this->created_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
