<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'slug',
    'description',
    'stream_url',
    'website_url',
    'artwork_url',
    'frequency',
    'is_live',
    'is_active',
    'sort_order',
])]
class RadioStream extends Model
{
    protected function casts(): array
    {
        return [
            'is_live' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
