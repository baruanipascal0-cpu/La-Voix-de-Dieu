<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCallStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'call_id' => ['required_without_all:callId,uuid'],
            'callId' => ['required_without_all:call_id,uuid'],
            'uuid' => ['required_without_all:call_id,callId'],
            'status' => ['required', Rule::in(['ringing', 'active', 'ended', 'missed', 'declined', 'cancelled'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
