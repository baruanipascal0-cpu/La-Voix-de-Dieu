<?php

namespace App\Http\Requests\Api\V1;

class StoreDirectMessageRequest extends StoreChatMessageRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'recipient_id' => ['required_without_all:recipientId,member_id,memberId,conversation_id,conversationId,uuid', 'integer', 'exists:users,id'],
            'recipientId' => ['required_without_all:recipient_id,member_id,memberId,conversation_id,conversationId,uuid', 'integer', 'exists:users,id'],
            'member_id' => ['required_without_all:recipient_id,recipientId,memberId,conversation_id,conversationId,uuid', 'integer', 'exists:users,id'],
            'memberId' => ['required_without_all:recipient_id,recipientId,member_id,conversation_id,conversationId,uuid', 'integer', 'exists:users,id'],
            'conversation_id' => ['nullable', 'string'],
            'conversationId' => ['nullable', 'string'],
            'uuid' => ['nullable', 'string'],
        ]);
    }
}
