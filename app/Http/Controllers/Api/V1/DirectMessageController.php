<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Social\BaseSocialController;
use App\Http\Requests\Api\V1\StoreChatMessageRequest;
use App\Http\Requests\Api\V1\StoreDirectMessageRequest;
use App\Models\ChatMessage;
use App\Models\DirectConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectMessageController extends BaseSocialController
{
    public function contacts(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->integer('per_page', 30), 100);
        $search = $request->string('q')->toString() ?: $request->string('search')->toString();

        $contacts = User::query()
            ->whereKeyNot($user->id)
            ->where('is_active', true)
            ->whereNull('suspended_at')
            ->whereNull('blocked_at')
            ->when(filled($search), function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query
                        ->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $data = $contacts->getCollection()
            ->map(fn (User $contact): array => $this->contactPayload($contact))
            ->values();

        return response()->json([
            'data' => $data,
            'contacts' => $data,
            'members' => $data,
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
            ],
        ]);
    }
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
    private function contactPayload(User $contact): array
    {
        $online = $contact->last_seen_at?->greaterThanOrEqualTo(now()->subMinutes(5)) ?? false;

        return [
            'id' => $contact->id,
            'user_id' => $contact->id,
            'userId' => $contact->id,
            'name' => $contact->name,
            'full_name' => $contact->name,
            'fullName' => $contact->name,
            'avatar' => $contact->avatar_url,
            'avatar_url' => $contact->avatar_url,
            'avatarUrl' => $contact->avatar_url,
            'photo_url' => $contact->avatar_url,
            'photoUrl' => $contact->avatar_url,
            'status' => $online ? 'online' : 'offline',
            'is_active' => $contact->is_active,
            'isActive' => $contact->is_active,
            'last_seen_at' => $contact->last_seen_at?->toISOString(),
            'lastSeenAt' => $contact->last_seen_at?->toISOString(),
        ];
    }
}
