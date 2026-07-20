<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['nullable', 'string', Rule::in(['android', 'ios', 'web', 'unknown'])],
            'device_id' => ['nullable', 'string', 'max:255'],
            'deviceId' => ['nullable', 'string', 'max:255'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'appVersion' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('deviceId') && ! $this->has('device_id')) {
            $payload['device_id'] = $this->input('deviceId');
        }

        if ($this->has('appVersion') && ! $this->has('app_version')) {
            $payload['app_version'] = $this->input('appVersion');
        }

        $this->merge($payload);
    }
}
