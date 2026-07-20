<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'token' => ['nullable', 'string', 'max:4096'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'deviceId' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('deviceId') && ! $this->has('device_id')) {
            $this->merge([
                'device_id' => $this->input('deviceId'),
            ]);
        }
    }
}
