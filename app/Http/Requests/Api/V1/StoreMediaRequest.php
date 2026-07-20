<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:204800',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,audio/mpeg,audio/mp4,audio/wav,audio/ogg,audio/webm,video/mp4,video/quicktime,video/webm,application/pdf',
            ],
            'collection' => ['nullable', 'string', 'max:80'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
