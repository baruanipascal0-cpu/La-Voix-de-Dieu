<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\+243\d{9}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Le phone requis (format +243XXXXXXXXX ou 0XXXXXXXXX).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('password_confirm') && ! $this->has('password_confirmation')) {
            $payload['password_confirmation'] = $this->input('password_confirm');
        }

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
