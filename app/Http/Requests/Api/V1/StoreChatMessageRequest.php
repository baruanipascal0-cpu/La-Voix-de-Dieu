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
            'metadata' => $this->input('metadata', []),
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
