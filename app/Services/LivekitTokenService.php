<?php

namespace App\Services;

use App\Models\CallSession;
use App\Models\PrayerRoom;
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
        return $this->makeRoomToken($user, $call->room_name, [
            'call_id' => $call->id,
            'call_uuid' => $call->uuid,
        ]);
    }

    public function makePrayerRoomToken(User $user, PrayerRoom $room): ?string
    {
        return $this->makeRoomToken($user, $this->prayerRoomName($room), [
            'prayer_room_id' => $room->id,
            'prayer_room_slug' => $room->slug,
        ]);
    }

    public function prayerRoomName(PrayerRoom $room): string
    {
        return $room->livekit_room ?: 'prayer-room-'.$room->id;
    }

    private function makeRoomToken(User $user, string $roomName, array $metadata = []): ?string
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
                'room' => $roomName,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => true,
            ],
            'metadata' => json_encode(array_merge([
                'user_id' => $user->id,
            ], $metadata), JSON_THROW_ON_ERROR),
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