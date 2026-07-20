<?php

namespace Tests\Feature\Api;

use App\Models\ChurchMember;
use App\Models\PrayerRoom;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StabilizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_members_endpoint_never_exposes_private_contact_fields(): void
    {
        ChurchMember::create([
            'display_name' => 'Membre visible',
            'phone' => '+243990000001',
            'email' => 'private@example.com',
            'show_contacts' => true,
        ]);

        $this
            ->getJson('/api/v1/public/members/')
            ->assertOk()
            ->assertJsonMissingPath('members.0.phone')
            ->assertJsonMissingPath('members.0.email')
            ->assertDontSee('+243990000001')
            ->assertDontSee('private@example.com');
    }

    public function test_prayer_room_launch_requires_auth_and_permission(): void
    {
        $this->seed(PermissionSeeder::class);

        PrayerRoom::create([
            'title' => 'Priere securisee',
            'slug' => 'priere-securisee',
            'meeting_url' => 'https://meet.example.test/secure',
        ]);

        $userWithoutPermission = User::factory()->create();
        $tokenWithoutPermission = $userWithoutPermission->createToken('mobile')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$tokenWithoutPermission)
            ->postJson('/api/v1/public/prayer-rooms/launch/', [
                'slug' => 'priere-securisee',
            ])
            ->assertForbidden();

        $this->forgetAuthGuards();

        $member = User::factory()->create();
        $member->assignRole('member');
        $memberToken = $member->createToken('mobile')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$memberToken)
            ->postJson('/api/v1/public/prayer-rooms/launch/', [
                'slug' => 'priere-securisee',
            ])
            ->assertOk()
            ->assertJsonPath('data.canJoin', true);
    }

    public function test_blocked_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'blocked@example.com',
            'phone' => '+243891500999',
            'password' => 'password123',
            'blocked_at' => now(),
        ]);

        $this
            ->postJson('/api/v1/auth/login/', [
                'email' => 'blocked@example.com',
                'password' => 'password123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_removed_public_root_aliases_are_not_registered(): void
    {
        $this->getJson('/api/v1/programme/')->assertNotFound();
        $this->getJson('/api/v1/quotes/')->assertNotFound();
    }

    private function forgetAuthGuards(): void
    {
        $this->app['auth']->forgetGuards();
    }
}
