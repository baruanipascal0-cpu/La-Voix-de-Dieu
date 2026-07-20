<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'first_name',
    'last_name',
    'display_name',
    'phone',
    'email',
    'avatar_url',
    'jurisdiction',
    'gender',
    'member_type',
    'joined_at',
    'show_contacts',
    'is_active',
])]
class ChurchMember extends Model
{
    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'show_contacts' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
