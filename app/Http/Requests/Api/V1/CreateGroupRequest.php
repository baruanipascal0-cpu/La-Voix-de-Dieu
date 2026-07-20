<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:social_groups,slug'],
            'description' => ['nullable', 'string'],
            'avatar_url' => ['nullable', 'url'],
            'avatarUrl' => ['nullable', 'url'],
            'cover_url' => ['nullable', 'url'],
            'coverUrl' => ['nullable', 'url'],
            'type' => ['nullable', 'string', 'max:50'],
            'is_public' => ['nullable', 'boolean'],
            'isPublic' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'requiresApproval' => ['nullable', 'boolean'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'memberIds' => ['nullable', 'array'],
            'memberIds.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function groupPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? $validated['avatarUrl'] ?? null,
            'cover_url' => $validated['cover_url'] ?? $validated['coverUrl'] ?? null,
            'type' => $validated['type'] ?? 'community',
            'is_public' => $validated['is_public'] ?? $validated['isPublic'] ?? true,
            'requires_approval' => $validated['requires_approval'] ?? $validated['requiresApproval'] ?? false,
            'member_ids' => $validated['member_ids'] ?? $validated['memberIds'] ?? [],
        ];
    }
}
