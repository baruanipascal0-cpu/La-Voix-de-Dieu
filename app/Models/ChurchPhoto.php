<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'caption',
    'image_url',
    'thumbnail_url',
    'taken_at',
    'sort_order',
    'is_published',
])]
class ChurchPhoto extends Model
{
    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}
