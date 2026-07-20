<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'day_of_week' => $this->day_of_week,
            'dayOfWeek' => $this->day_of_week,
            'starts_at' => $this->timeValue($this->starts_at),
            'startsAt' => $this->timeValue($this->starts_at),
            'ends_at' => $this->timeValue($this->ends_at),
            'endsAt' => $this->timeValue($this->ends_at),
            'location' => $this->location,
            'speaker' => $this->speaker,
            'image_url' => $this->image_url,
            'imageUrl' => $this->image_url,
            'image' => $this->image_url,
            'is_featured' => $this->is_featured,
            'isFeatured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'sortOrder' => $this->sort_order,
        ];
    }

    private function timeValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }
}
