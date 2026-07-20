<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'call_type' => ['nullable', Rule::in(['dm', 'group', 'public'])],
            'callType' => ['nullable', Rule::in(['dm', 'group', 'public'])],
            'recipient_id' => ['nullable', 'integer', 'exists:users,id'],
            'recipientId' => ['nullable', 'integer', 'exists:users,id'],
            'group_id' => ['nullable'],
            'groupId' => ['nullable'],
            'title' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', Rule::in(['ringing', 'active'])],
            'room_name' => ['nullable', 'string', 'max:180'],
            'roomName' => ['nullable', 'string', 'max:180'],
            'channel_name' => ['nullable', 'string', 'max:180'],
            'channelName' => ['nullable', 'string', 'max:180'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
