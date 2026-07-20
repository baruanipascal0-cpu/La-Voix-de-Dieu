<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SermonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'preacher' => $this->preacher,
            'speaker' => $this->preacher,
            'scripture_reference' => $this->scripture_reference,
            'scriptureReference' => $this->scripture_reference,
            'audio_url' => $this->audio_url,
            'audioUrl' => $this->audio_url,
            'video_url' => $this->video_url,
            'videoUrl' => $this->video_url,
            'youtube_url' => $this->youtube_url,
            'youtubeUrl' => $this->youtube_url,
            'youtube_id' => $this->youtube_id,
            'youtubeId' => $this->youtube_id,
            'thumbnail_url' => $this->thumbnail_url,
            'thumbnailUrl' => $this->thumbnail_url,
            'image' => $this->thumbnail_url,
            'duration_seconds' => $this->duration_seconds,
            'durationSeconds' => $this->duration_seconds,
            'is_featured' => $this->is_featured,
            'isFeatured' => $this->is_featured,
            'published_at' => $this->published_at?->toISOString(),
            'publishedAt' => $this->published_at?->toISOString(),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
        ];
    }
}
