<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'verse',
    'reference',
    'version',
    'image_url',
    'verse_date',
    'sort_order',
    'is_active',
])]
class DailyVerse extends Model
{
    protected function casts(): array
    {
        return [
            'verse_date' => 'date',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
