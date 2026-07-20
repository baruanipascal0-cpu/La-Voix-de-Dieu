<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid',
    'scope',
    'social_group_id',
    'direct_conversation_id',
    'call_session_id',
    'sender_id',
    'body',
    'message_type',
    'media_url',
    'metadata',
    'status',
    'reported_at',
    'moderated_by',
    'moderated_at',
    'moderation_reason',
    'edited_at',
])]
class ChatMessage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'reported_at' => 'datetime',
            'moderated_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SocialGroup::class, 'social_group_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(DirectConversation::class, 'direct_conversation_id');
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(CallSession::class, 'call_session_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
