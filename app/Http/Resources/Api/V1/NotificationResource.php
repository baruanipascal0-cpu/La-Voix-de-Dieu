<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data ?? [],
            'channel' => $this->channel,
            'status' => $this->status,
            'sent_at' => $this->sent_at?->toISOString(),
            'sentAt' => $this->sent_at?->toISOString(),
            'read_at' => $this->read_at?->toISOString(),
            'readAt' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
