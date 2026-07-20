<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'description',
    'location',
    'event_type',
    'starts_at',
    'ends_at',
    'is_public',
    'is_active',
])]
class PastorCalendarEvent extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
