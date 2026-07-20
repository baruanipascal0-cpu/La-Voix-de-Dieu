<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    private const ADMIN_EMAIL = 'admin@lavoixdedieu.org';

    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        if ($this->activeAdminExists()) {
            return;
        }

        $admin = User::query()->firstOrNew([
            'email' => self::ADMIN_EMAIL,
        ]);

        $admin->forceFill([
            'name' => 'Administrator',
            'email' => self::ADMIN_EMAIL,
            'password' => Hash::make('Admin@123456'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'suspended_at' => null,
            'blocked_at' => null,
        ])->save();

        $admin->syncRoles(['super_admin']);
    }

    private function activeAdminExists(): bool
    {
        return User::query()
            ->where('is_active', true)
            ->whereNull('suspended_at')
            ->whereNull('blocked_at')
            ->where(function ($query): void {
                $query
                    ->whereIn('role', ['super_admin', 'admin'])
                    ->orWhereHas('roles', function ($query): void {
                        $query->whereIn('name', ['super_admin', 'admin']);
                    });
            })
            ->exists();
    }
}