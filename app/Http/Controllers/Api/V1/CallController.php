<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\CallSessionUpdated;
use App\Events\CallSignalSent;
use App\Http\Controllers\Api\V1\Social\BaseSocialController;
use App\Http\Requests\Api\V1\CreateCallRequest;
use App\Http\Requests\Api\V1\StoreCallSignalRequest;
use App\Http\Requests\Api\V1\UpdateCallStateRequest;
use App\Models\CallSession;
use App\Models\CallSignal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends BaseSocialController
{
    public function publicIndex(Request $request): JsonResponse
    {
        $calls = CallSession::query()
            ->with(['initiator', 'recipient', 'group'])
            ->where(function (Builder $query): void {
                $query
                    ->where('call_type', 'public')
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->where('call_type', 'group')
                            ->whereHas('group', fn (Builder $group): Builder => $group
                                ->where('is_public', true)
                                ->where('status', 'approved'));
                    });
            })
            ->whereNotIn('status', ['ended', 'declined', 'cancelled'])
            ->orderByDesc('created_at')
            ->limit(min((int) $request->integer('limit', 30), 100))
            ->get()
            ->map(fn (CallSession $call): array => $this->callPayload($call, $request))
            ->values();

        return response()->json([
            'data' => $calls,
            'calls' => $calls,
        ]);
    }

    public function publicStore(CreateCallRequest $request): JsonResponse
    {
        $call = $this->createCallSession($request, 'public');

        return response()->json([
            'message' => 'Appel cree.',
            'data' => $this->callPayload($call, $request),
            'call' => $this->callPayload($call, $request),
        ], 201);
    }

    public function dmIndex(Request $request): JsonResponse
    {
        $calls = CallSession::query()
            ->with(['initiator', 'recipient', 'conversation.participants'])
            ->where(function (Builder $query) use ($request): void {
                $query
                    ->where('initiator_id', $request->user()->id)
                    ->orWhere('recipient_id', $request->user()->id);
            })
            ->where('call_type', 'dm')
            ->orderByDesc('created_at')
            ->limit(min((int) $request->integer('limit', 30), 100))
            ->get()
            ->map(fn (CallSession $call): array => $this->callPayload($call, $request))
            ->values();

        return response()->json([
            'data' => $calls,
            'calls' => $calls,
        ]);
    }

    public function dmStore(CreateCallRequest $request): JsonResponse
    {
        $call = $this->createCallSession($request, 'dm');

        return response()->json([
            'message' => 'Appel direct cree.',
            'data' => $this->callPayload($call, $request),
            'call' => $this->callPayload($call, $request),
        ], 201);
    }

    public function markDmSeen(Request $request): JsonResponse
    {
        $request->user()->forceFill(['last_seen_at' => now()])->save();

        return response()->json([
            'message' => 'Appels vus.',
            'data' => [
                'seen_at' => now()->toISOString(),
                'seenAt' => now()->toISOString(),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $calls = CallSession::query()
            ->with(['initiator', 'recipient', 'group'])
            ->where(function (Builder $query) use ($request): void {
                $query
                    ->where('initiator_id', $request->user()->id)
                    ->orWhere('recipient_id', $request->user()->id)
                    ->orWhereHas('group.members', fn (Builder $query) => $query->where('users.id', $request->user()->id));
            })
            ->orderByDesc('created_at')
            ->limit(min((int) $request->integer('limit', 50), 100))
            ->get()
            ->map(fn (CallSession $call): array => $this->callPayload($call, $request))
            ->values();

        return response()->json([
            'data' => $calls,
            'calls' => $calls,
        ]);
    }

    public function store(CreateCallRequest $request): JsonResponse
    {
        $call = $this->createCallSession($request, $request->input('call_type', $request->input('callType', 'dm')));

        return response()->json([
            'message' => 'Appel cree.',
            'data' => $this->callPayload($call, $request),
            'call' => $this->callPayload($call, $request),
        ], 201);
    }

    public function storeSignal(StoreCallSignalRequest $request): JsonResponse
    {
        $call = $this->findCallForUser($request, $request->user());
        $validated = $request->validated();

        $signal = CallSignal::create([
            'call_session_id' => $call->id,
            'sender_id' => $request->user()->id,
            'recipient_id' => $validated['recipient_id'] ?? $validated['recipientId'] ?? null,
            'signal_type' => $validated['signal_type'] ?? $validated['signalType'] ?? $validated['type'] ?? 'signal',
            'payload' => $this->normalizePayload($request->input('payload', [])),
        ]);

        event(new CallSignalSent($signal));

        return response()->json([
            'message' => 'Signal enregistre.',
            'data' => $this->signalPayload($signal->load('sender', 'recipient')),
            'signal' => $this->signalPayload($signal),
        ], 201);
    }

    public function updateState(UpdateCallStateRequest $request): JsonResponse
    {
        $call = $this->findCallForUser($request, $request->user());
        $validated = $request->validated();

        $attributes = [
            'status' => $validated['status'],
            'last_state_at' => now(),
            'metadata' => array_merge($call->metadata ?? [], $validated['metadata'] ?? []),
        ];

        if ($validated['status'] === 'active' && ! $call->started_at) {
            $attributes['started_at'] = now();
        }

        if (in_array($validated['status'], ['ended', 'missed', 'declined', 'cancelled'], true)) {
            $attributes['ended_at'] = now();
        }

        $call->forceFill($attributes)->save();
        $call = $call->fresh(['initiator', 'recipient', 'group']);

        event(new CallSessionUpdated($call));

        return response()->json([
            'message' => 'Etat de l appel mis a jour.',
            'data' => $this->callPayload($call, $request),
            'call' => $this->callPayload($call, $request),
        ]);
    }
}
