<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_creates_requested_filament_admin_when_none_exists(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@lavoixdedieu.org')->firstOrFail();

        $this->assertSame('Administrator', $admin->name);
        $this->assertSame('super_admin', $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('Admin@123456', $admin->password));
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->hasPermissionTo('access admin'));
    }

    public function test_admin_login_route_is_registered(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}