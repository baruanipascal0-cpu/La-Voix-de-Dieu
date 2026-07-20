<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id',
    'title',
    'slug',
    'subtitle',
    'description',
    'preacher',
    'scripture_reference',
    'audio_url',
    'video_url',
    'youtube_url',
    'youtube_id',
    'thumbnail_url',
    'duration_seconds',
    'published_at',
    'is_featured',
    'is_published',
    'sort_order',
])]
class Sermon extends Model
{
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
