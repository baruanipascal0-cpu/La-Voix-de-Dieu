<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'call_type',
    'status',
    'title',
    'initiator_id',
    'recipient_id',
    'social_group_id',
    'direct_conversation_id',
    'provider',
    'room_name',
    'channel_name',
    'started_at',
    'ended_at',
    'last_state_at',
    'metadata',
])]
class CallSession extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'last_state_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SocialGroup::class, 'social_group_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(DirectConversation::class, 'direct_conversation_id');
    }

    public function signals(): HasMany
    {
        return $this->hasMany(CallSignal::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
