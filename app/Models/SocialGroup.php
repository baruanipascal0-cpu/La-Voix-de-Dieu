<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'avatar_url',
    'cover_url',
    'type',
    'status',
    'is_public',
    'requires_approval',
    'is_active',
    'created_by',
    'approved_by',
    'approved_at',
    'suspended_at',
    'blocked_at',
    'moderation_reason',
    'sort_order',
])]
class SocialGroup extends Model
{
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
            'blocked_at' => 'datetime',
            'is_public' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'social_group_members')
            ->withPivot(['role', 'status', 'joined_at', 'last_read_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
