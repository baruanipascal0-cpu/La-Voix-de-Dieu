<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrayerRoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'room_type' => $this->room_type,
            'roomType' => $this->room_type,
            'meeting_url' => $this->meeting_url,
            'meetingUrl' => $this->meeting_url,
            'livekit_room' => $this->livekit_room,
            'livekitRoom' => $this->livekit_room,
            'starts_at' => $this->starts_at?->toISOString(),
            'startsAt' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'endsAt' => $this->ends_at?->toISOString(),
            'is_live' => $this->is_live,
            'isLive' => $this->is_live,
        ];
    }
}
