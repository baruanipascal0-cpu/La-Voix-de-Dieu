<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class LaunchPrayerRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        try {
            return $user->hasPermissionTo('launch prayer rooms', 'web');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function rules(): array
    {
        return [
            'room_id' => ['nullable', 'integer', 'exists:prayer_rooms,id'],
            'roomId' => ['nullable', 'integer', 'exists:prayer_rooms,id'],
            'slug' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function roomId(): ?int
    {
        return $this->integer('room_id') ?: ($this->integer('roomId') ?: null);
    }
}
