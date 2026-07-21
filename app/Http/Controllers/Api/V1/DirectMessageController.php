<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Social\BaseSocialController;
use App\Http\Requests\Api\V1\StoreChatMessageRequest;
use App\Http\Requests\Api\V1\StoreDirectMessageRequest;
use App\Models\ChatMessage;
use App\Models\DirectConversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectMessageController extends BaseSocialController
{
    public function index(Request $request): JsonResponse
    {
        $threads = DirectConversation::query()
            ->with('participants')
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $request->user()->id))
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (DirectConversation $conversation): array => $this->conversationPayload($conversation, $request->user(), request: $request))
            ->values();

        return response()->json([
            'data' => $threads,
            'threads' => $threads,
            'conversations' => $threads,
        ]);
    }

    public function store(StoreDirectMessageRequest $request): JsonResponse
    {
        $conversation = $this->resolveConversationForMessage($request);
        $stored = $this->storeDirectMessage($request, $conversation);
        $message = $stored['message'];
        $conversation = $stored['conversation'];

        return response()->json([
            'message' => 'Message prive envoye.',
            'data' => $this->messagePayload($message->load('sender', 'conversation.participants'), $request),
            'chat_message' => $this->messagePayload($message, $request),
            'chatMessage' => $this->messagePayload($message, $request),
            'conversation' => $this->conversationPayload($conversation, $request->user(), request: $request),
        ], 201);
    }

    public function show(Request $request, string $conversation): JsonResponse
    {
        $conversation = $this->findConversationForUser($conversation, $request->user());

        $messages = $this->recentMessages(
            ChatMessage::query()
                ->where('scope', 'dm')
                ->where('direct_conversation_id', $conversation->id),
            $request,
        );

        return response()->json([
            'data' => $this->conversationPayload($conversation->load('participants'), $request->user(), $messages, $request),
            'conversation' => $this->conversationPayload($conversation, $request->user(), $messages, $request),
            'messages' => $messages,
        ]);
    }

    public function storeInThread(StoreChatMessageRequest $request, string $conversation): JsonResponse
    {
        $conversation = $this->findConversationForUser($conversation, $request->user());
        $stored = $this->storeDirectMessage($request, $conversation);
        $message = $stored['message'];
        $conversation = $stored['conversation'];

        return response()->json([
            'message' => 'Message prive envoye.',
            'data' => $this->messagePayload($message->load('sender', 'conversation.participants'), $request),
            'chat_message' => $this->messagePayload($message, $request),
            'chatMessage' => $this->messagePayload($message, $request),
            'conversation' => $this->conversationPayload($conversation, $request->user(), request: $request),
        ], 201);
    }
}
