<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallSignalRequest extends FormRequest
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
            'type' => ['nullable', 'string', 'max:80'],
            'signal_type' => ['nullable', 'string', 'max:80'],
            'signalType' => ['nullable', 'string', 'max:80'],
            'recipient_id' => ['nullable', 'integer', 'exists:users,id'],
            'recipientId' => ['nullable', 'integer', 'exists:users,id'],
            'payload' => ['nullable'],
        ];
    }
}
