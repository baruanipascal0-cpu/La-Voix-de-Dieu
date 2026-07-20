<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'author',
    'role',
    'content',
    'image_url',
    'published_at',
    'sort_order',
    'is_published',
])]
class Testimonial extends Model
{
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}
