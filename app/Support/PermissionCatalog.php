<?php

namespace App\Support;

class PermissionCatalog
{
    public const PERMISSIONS = [
        'access admin',
        'manage users',
        'manage content',
        'manage church',
        'manage social',
        'manage media',
        'manage notifications',
        'manage prayer',
        'manage settings',
        'launch prayer rooms',
        'create social groups',
        'approve social groups',
        'moderate content',
        'suspend users',
        'block users',
    ];

    public const ROLES = [
        'super_admin' => self::PERMISSIONS,
        'admin' => [
            'access admin',
            'manage users',
            'manage content',
            'manage church',
            'manage social',
            'manage media',
            'manage notifications',
            'manage prayer',
            'launch prayer rooms',
            'create social groups',
            'approve social groups',
            'moderate content',
            'suspend users',
            'block users',
        ],
        'editor' => [
            'access admin',
            'manage content',
            'manage church',
            'manage media',
        ],
        'moderator' => [
            'access admin',
            'manage social',
            'manage notifications',
            'approve social groups',
            'moderate content',
            'suspend users',
        ],
        'media_manager' => [
            'access admin',
            'manage media',
            'manage content',
        ],
        'prayer_leader' => [
            'access admin',
            'manage prayer',
            'manage church',
            'launch prayer rooms',
        ],
        'member' => [
            'launch prayer rooms',
        ],
    ];
}
