<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Social\BaseSocialController;
use App\Http\Requests\Api\V1\StoreChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\ChatReadReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicChatController extends BaseSocialController
{
    public function index(Request $request): JsonResponse
    {
        $messages = $this->recentMessages(
            ChatMessage::query()
                ->where('scope', 'public')
                ->whereNull('social_group_id')
                ->whereNull('direct_conversation_id')
                ->whereNull('call_session_id'),
            $request,
        );

        return response()->json([
            'data' => $messages,
            'messages' => $messages,
            'chat' => $messages,
        ]);
    }

    public function store(StoreChatMessageRequest $request): JsonResponse
    {
        $validated = $request->messagePayload();

        $message = $this->createMessage([
            'scope' => 'public',
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
            'message_type' => $validated['message_type'],
            'media_url' => $validated['media_url'],
            'metadata' => $validated['metadata'],
        ]);

        return response()->json([
            'message' => 'Message envoye.',
            'data' => $this->messagePayload($message->load('sender'), $request),
            'chat_message' => $this->messagePayload($message, $request),
            'chatMessage' => $this->messagePayload($message, $request),
        ], 201);
    }

    public function markSeen(Request $request): JsonResponse
    {
        $receipt = ChatReadReceipt::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'scope' => 'public',
                'social_group_id' => null,
                'direct_conversation_id' => null,
                'call_session_id' => null,
            ],
            [
                'last_seen_message_id' => $request->integer('message_id') ?: null,
                'last_seen_at' => now(),
            ],
        );

        return response()->json([
            'message' => 'Lecture enregistree.',
            'data' => $this->receiptPayload($receipt),
            'seen' => $this->receiptPayload($receipt),
        ]);
    }
}
