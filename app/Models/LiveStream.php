<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'slug',
    'description',
    'stream_url',
    'playback_url',
    'youtube_url',
    'youtube_id',
    'thumbnail_url',
    'platform',
    'starts_at',
    'ends_at',
    'is_live',
    'is_published',
    'sort_order',
])]
class LiveStream extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_live' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
