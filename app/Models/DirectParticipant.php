<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'direct_conversation_id',
    'user_id',
    'last_read_at',
    'muted_at',
])]
class DirectParticipant extends Model
{
    protected $table = 'direct_conversation_user';

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'muted_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(DirectConversation::class, 'direct_conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
