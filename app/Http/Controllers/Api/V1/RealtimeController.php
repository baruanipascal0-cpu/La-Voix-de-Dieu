<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\User;
use App\Services\LivekitTokenService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    public function config(Request $request, LivekitTokenService $livekit): JsonResponse
    {
        $user = $request->user();
        $broadcastDriver = config('broadcasting.default', 'log') ?: 'log';

        $groupChannels = $user->socialGroups()
            ->wherePivot('status', 'active')
            ->pluck('social_groups.id')
            ->map(fn (int $id): string => 'private-groups.'.$id)
            ->values();

        $dmChannels = $user->directConversations()
            ->pluck('direct_conversations.id')
            ->map(fn (int $id): string => 'private-dm.'.$id)
            ->values();

        return response()->json([
            'data' => [
                'broadcasting' => [
                    'driver' => $broadcastDriver,
                    'auth_endpoint' => url('/broadcasting/auth'),
                    'authEndpoint' => url('/broadcasting/auth'),
                    'public_channels' => ['public.chat', 'public.calls'],
                    'publicChannels' => ['public.chat', 'public.calls'],
                    'private_channels' => collect(['private-users.'.$user->id])
                        ->merge($groupChannels)
                        ->merge($dmChannels)
                        ->values(),
                    'privateChannels' => collect(['private-users.'.$user->id])
                        ->merge($groupChannels)
                        ->merge($dmChannels)
                        ->values(),
                    'reverb' => $this->reverbPayload($broadcastDriver),
                ],
                'livekit' => [
                    'url' => $livekit->url(),
                    'configured' => $livekit->configured(),
                ],
            ],
        ]);
    }

    public function callToken(Request $request, string $call, LivekitTokenService $livekit): JsonResponse
    {
        $callSession = $this->findCallForUser($call, $request->user());

        return response()->json([
            'data' => [
                'provider' => 'livekit',
                'configured' => $livekit->configured(),
                'url' => $livekit->url(),
                'room_name' => $callSession->room_name,
                'roomName' => $callSession->room_name,
                'token' => $livekit->makeToken($request->user(), $callSession),
                'call_id' => $callSession->id,
                'callId' => $callSession->id,
                'uuid' => $callSession->uuid,
            ],
        ]);
    }

    private function findCallForUser(string $call, User $user): CallSession
    {
        $callSession = CallSession::query()
            ->where(function (Builder $query) use ($call): void {
                $query->where('uuid', $call);

                if (ctype_digit($call)) {
                    $query->orWhere('id', (int) $call);
                }
            })
            ->with(['group.members'])
            ->firstOrFail();

        $allowed = $callSession->call_type === 'public'
            || $callSession->initiator_id === $user->id
            || $callSession->recipient_id === $user->id
            || $callSession->group?->members->contains('id', $user->id);

        abort_unless($allowed, 403);

        return $callSession;
    }

    private function reverbPayload(?string $broadcastDriver): ?array
    {
        if ($broadcastDriver !== 'reverb') {
            return null;
        }

        $key = config('broadcasting.connections.reverb.key');
        $host = config('broadcasting.connections.reverb.options.host');
        $port = (int) config('broadcasting.connections.reverb.options.port', 443);
        $scheme = (string) config('broadcasting.connections.reverb.options.scheme', 'https');
        $useTls = $scheme === 'https';
        $wsScheme = $useTls ? 'wss' : 'ws';
        $wsUrl = filled($host) && filled($key)
            ? $wsScheme.'://'.$host.':'.$port.'/app/'.$key
            : null;

        return [
            'app_key' => $key,
            'appKey' => $key,
            'host' => $host,
            'port' => $port,
            'scheme' => $scheme,
            'use_tls' => $useTls,
            'useTLS' => $useTls,
            'ws_url' => $wsUrl,
            'wsUrl' => $wsUrl,
        ];
    }
}
