<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMediaRequest;
use App\Models\MediaUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $uploads = MediaUpload::query()
            ->where('user_id', $request->user()->id)
            ->when($request->filled('collection'), fn ($query) => $query->where('collection', $request->string('collection')->toString()))
            ->latest()
            ->limit(min((int) $request->integer('limit', 50), 100))
            ->get()
            ->map(fn (MediaUpload $upload): array => $this->payload($upload))
            ->values();

        return response()->json([
            'data' => $uploads,
            'uploads' => $uploads,
        ]);
    }

    public function store(StoreMediaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $file = $validated['file'];
        $collection = Str::slug($validated['collection'] ?? 'general') ?: 'general';
        $directory = 'uploads/'.$request->user()->id.'/'.$collection.'/'.now()->format('Y/m/d');
        $path = $file->storePublicly($directory, ['disk' => 'public']);

        $upload = MediaUpload::create([
            'user_id' => $request->user()->id,
            'collection' => $collection,
            'disk' => 'public',
            'visibility' => 'public',
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize() ?: 0,
            'metadata' => $validated['metadata'] ?? [],
        ]);

        return response()->json([
            'message' => 'Media envoye.',
            'data' => $this->payload($upload),
            'upload' => $this->payload($upload),
            'media_url' => $upload->url,
            'mediaUrl' => $upload->url,
        ], 201);
    }

    public function destroy(Request $request, MediaUpload $mediaUpload): JsonResponse
    {
        abort_unless(
            $mediaUpload->user_id === $request->user()->id || $request->user()->can('manage media'),
            403,
        );

        Storage::disk($mediaUpload->disk)->delete($mediaUpload->path);
        $mediaUpload->delete();

        return response()->json([
            'message' => 'Media supprime.',
        ]);
    }

    private function payload(MediaUpload $upload): array
    {
        return [
            'id' => $upload->id,
            'collection' => $upload->collection,
            'disk' => $upload->disk,
            'path' => $upload->path,
            'url' => $upload->url,
            'media_url' => $upload->url,
            'mediaUrl' => $upload->url,
            'original_name' => $upload->original_name,
            'originalName' => $upload->original_name,
            'mime_type' => $upload->mime_type,
            'mimeType' => $upload->mime_type,
            'file_size' => $upload->file_size,
            'fileSize' => $upload->file_size,
            'metadata' => $upload->metadata ?? [],
            'created_at' => $upload->created_at?->toISOString(),
            'createdAt' => $upload->created_at?->toISOString(),
        ];
    }
}
