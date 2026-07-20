<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'scope',
    'social_group_id',
    'direct_conversation_id',
    'call_session_id',
    'last_seen_message_id',
    'last_seen_at',
])]
class ChatReadReceipt extends Model
{
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastSeenMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'last_seen_message_id');
    }
}
