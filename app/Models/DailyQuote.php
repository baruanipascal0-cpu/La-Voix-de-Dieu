<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'quote',
    'reference',
    'author',
    'image_url',
    'quote_date',
    'sort_order',
    'is_active',
])]
class DailyQuote extends Model
{
    protected function casts(): array
    {
        return [
            'quote_date' => 'date',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
