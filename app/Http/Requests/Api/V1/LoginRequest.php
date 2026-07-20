<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'login' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('email')) {
            $payload['email'] = strtolower((string) $this->input('email'));
        }

        if ($this->has('phone')) {
            $payload['phone'] = $this->normalizePhone($this->input('phone'));
        }

        $this->merge($payload);
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = preg_replace('/[\s\-().]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+243'.substr($phone, 1);
        }

        return $phone;
    }
}
