<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SermonResource;
use App\Models\LiveStream;
use App\Models\RadioStream;
use App\Models\Sermon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicContentController extends Controller
{
    public function sermons(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 20), 50);

        $sermons = Sermon::query()
            ->with('category')
            ->where('is_published', true)
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->toString().'%';

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', $search)
                        ->orWhere('subtitle', 'like', $search)
                        ->orWhere('preacher', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->paginate($perPage);

        $data = $sermons->getCollection()
            ->map(fn (Sermon $sermon): array => SermonResource::make($sermon)->resolve($request))
            ->values();

        return response()->json([
            'data' => $data,
            'sermons' => $data,
            'meta' => [
                'current_page' => $sermons->currentPage(),
                'last_page' => $sermons->lastPage(),
                'per_page' => $sermons->perPage(),
                'total' => $sermons->total(),
            ],
        ]);
    }

    public function sermon(Sermon $sermon): JsonResponse
    {
        abort_unless($sermon->is_published, 404);

        return response()->json([
            'data' => SermonResource::make($sermon->load('category'))->resolve(request()),
            'sermon' => SermonResource::make($sermon)->resolve(request()),
        ]);
    }

    public function radio(): JsonResponse
    {
        $streams = RadioStream::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (RadioStream $stream): array => $this->radioPayload($stream))
            ->values();

        return response()->json([
            'data' => $streams,
            'radio' => $streams->first(),
            'streams' => $streams,
        ]);
    }

    public function live(): JsonResponse
    {
        $streams = LiveStream::query()
            ->where('is_published', true)
            ->orderByDesc('is_live')
            ->orderByDesc('starts_at')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (LiveStream $stream): array => $this->livePayload($stream))
            ->values();

        return response()->json([
            'data' => $streams,
            'live' => $streams->first(),
            'streams' => $streams,
        ]);
    }

    private function sermonPayload(Sermon $sermon): array
    {
        return [
            'id' => $sermon->id,
            'title' => $sermon->title,
            'slug' => $sermon->slug,
            'subtitle' => $sermon->subtitle,
            'description' => $sermon->description,
            'preacher' => $sermon->preacher,
            'speaker' => $sermon->preacher,
            'scripture_reference' => $sermon->scripture_reference,
            'audio_url' => $sermon->audio_url,
            'audioUrl' => $sermon->audio_url,
            'video_url' => $sermon->video_url,
            'videoUrl' => $sermon->video_url,
            'youtube_url' => $sermon->youtube_url,
            'youtubeUrl' => $sermon->youtube_url,
            'youtube_id' => $sermon->youtube_id,
            'youtubeId' => $sermon->youtube_id,
            'thumbnail_url' => $sermon->thumbnail_url,
            'thumbnailUrl' => $sermon->thumbnail_url,
            'image' => $sermon->thumbnail_url,
            'duration_seconds' => $sermon->duration_seconds,
            'durationSeconds' => $sermon->duration_seconds,
            'is_featured' => $sermon->is_featured,
            'isFeatured' => $sermon->is_featured,
            'published_at' => $sermon->published_at?->toISOString(),
            'publishedAt' => $sermon->published_at?->toISOString(),
            'category' => $sermon->category ? [
                'id' => $sermon->category->id,
                'name' => $sermon->category->name,
                'slug' => $sermon->category->slug,
            ] : null,
        ];
    }

    private function radioPayload(RadioStream $stream): array
    {
        return [
            'id' => $stream->id,
            'title' => $stream->title,
            'slug' => $stream->slug,
            'description' => $stream->description,
            'stream_url' => $stream->stream_url,
            'streamUrl' => $stream->stream_url,
            'url' => $stream->stream_url,
            'website_url' => $stream->website_url,
            'websiteUrl' => $stream->website_url,
            'artwork_url' => $stream->artwork_url,
            'artworkUrl' => $stream->artwork_url,
            'image' => $stream->artwork_url,
            'frequency' => $stream->frequency,
            'is_live' => $stream->is_live,
            'isLive' => $stream->is_live,
        ];
    }

    private function livePayload(LiveStream $stream): array
    {
        return [
            'id' => $stream->id,
            'title' => $stream->title,
            'slug' => $stream->slug,
            'description' => $stream->description,
            'stream_url' => $stream->stream_url,
            'streamUrl' => $stream->stream_url,
            'playback_url' => $stream->playback_url,
            'playbackUrl' => $stream->playback_url,
            'youtube_url' => $stream->youtube_url,
            'youtubeUrl' => $stream->youtube_url,
            'youtube_id' => $stream->youtube_id,
            'youtubeId' => $stream->youtube_id,
            'thumbnail_url' => $stream->thumbnail_url,
            'thumbnailUrl' => $stream->thumbnail_url,
            'image' => $stream->thumbnail_url,
            'platform' => $stream->platform,
            'starts_at' => $stream->starts_at?->toISOString(),
            'startsAt' => $stream->starts_at?->toISOString(),
            'ends_at' => $stream->ends_at?->toISOString(),
            'endsAt' => $stream->ends_at?->toISOString(),
            'is_live' => $stream->is_live,
            'isLive' => $stream->is_live,
        ];
    }
}
