<?php

namespace App\Services;

use App\Models\CallSession;
use App\Models\User;

class LivekitTokenService
{
    public function configured(): bool
    {
        return filled(config('services.livekit.url'))
            && filled(config('services.livekit.api_key'))
            && filled(config('services.livekit.api_secret'));
    }

    public function url(): ?string
    {
        return config('services.livekit.url');
    }

    public function makeToken(User $user, CallSession $call): ?string
    {
        if (! $this->configured()) {
            return null;
        }

        $now = time();
        $ttl = (int) config('services.livekit.token_ttl', 3600);

        $payload = [
            'iss' => config('services.livekit.api_key'),
            'sub' => (string) $user->id,
            'name' => $user->name,
            'nbf' => $now - 10,
            'exp' => $now + $ttl,
            'video' => [
                'roomJoin' => true,
                'room' => $call->room_name,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => true,
            ],
            'metadata' => json_encode([
                'user_id' => $user->id,
                'call_id' => $call->id,
                'call_uuid' => $call->uuid,
            ]),
        ];

        return $this->jwt($payload, (string) config('services.livekit.api_secret'));
    }

    private function jwt(array $payload, string $secret): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
