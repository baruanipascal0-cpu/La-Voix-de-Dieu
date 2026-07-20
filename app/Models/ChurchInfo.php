<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'tagline',
    'about',
    'address',
    'city',
    'country',
    'phone',
    'email',
    'website_url',
    'map_url',
    'latitude',
    'longitude',
    'logo_url',
    'cover_url',
    'service_times',
    'social_links',
    'is_active',
])]
class ChurchInfo extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'service_times' => 'array',
            'social_links' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
