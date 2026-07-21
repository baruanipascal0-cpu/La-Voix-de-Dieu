<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
            'message_type' => ['nullable', Rule::in(['text', 'audio', 'image', 'video', 'system'])],
            'messageType' => ['nullable', Rule::in(['text', 'audio', 'image', 'video', 'system'])],
            'type' => ['nullable', Rule::in(['text', 'audio', 'image', 'video', 'system'])],
            'media_url' => ['nullable', 'url'],
            'mediaUrl' => ['nullable', 'url'],
            'audio_url' => ['nullable', 'url'],
            'audioUrl' => ['nullable', 'url'],
            'media_id' => ['nullable', 'integer'],
            'mediaId' => ['nullable', 'integer'],
            'duration' => ['nullable', 'string', 'max:40'],
            'duration_label' => ['nullable', 'string', 'max:40'],
            'durationLabel' => ['nullable', 'string', 'max:40'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'mimeType' => ['nullable', 'string', 'max:120'],
            'file_name' => ['nullable', 'string', 'max:180'],
            'fileName' => ['nullable', 'string', 'max:180'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (blank($this->body()) && blank($this->mediaUrl())) {
                $validator->errors()->add('message', 'Le message ou le media est requis.');
            }
        });
    }

    public function messagePayload(): array
    {
        return [
            'body' => $this->body(),
            'message_type' => $this->input('message_type')
                ?? $this->input('messageType')
                ?? $this->input('type')
                ?? ($this->mediaUrl() ? 'audio' : 'text'),
            'media_url' => $this->mediaUrl(),
            'metadata' => array_filter(array_merge($this->input('metadata', []), [
                'media_id' => $this->input('media_id') ?? $this->input('mediaId'),
                'duration' => $this->input('duration') ?? $this->input('duration_label') ?? $this->input('durationLabel'),
                'mime_type' => $this->input('mime_type') ?? $this->input('mimeType'),
                'file_name' => $this->input('file_name') ?? $this->input('fileName'),
            ]), fn ($value): bool => filled($value)),
        ];
    }

    protected function body(): ?string
    {
        return $this->input('body') ?? $this->input('message') ?? $this->input('text');
    }

    protected function mediaUrl(): ?string
    {
        return $this->input('media_url')
            ?? $this->input('mediaUrl')
            ?? $this->input('audio_url')
            ?? $this->input('audioUrl');
    }
}
