<?php

namespace Tests\Feature\Api;

use App\Models\PushNotification;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\SendReport;
use Tests\TestCase;

class IntegrationInfrastructureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_media(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/v1/media/uploads/', [
                'file' => UploadedFile::fake()->create('voice.mp3', 128, 'audio/mpeg'),
                'collection' => 'chat-audio',
            ])
            ->assertCreated()
            ->assertJsonPath('data.collection', 'chat-audio');

        Storage::disk('public')->assertExists($response->json('data.path'));

        $this->assertDatabaseHas('media_uploads', [
            'user_id' => $user->id,
            'collection' => 'chat-audio',
            'mime_type' => 'audio/mpeg',
        ]);
    }

    public function test_direct_message_creates_notification_for_recipient(): void
    {
        $sender = User::factory()->create(['name' => 'Sender']);
        $recipient = User::factory()->create(['name' => 'Recipient']);
        $senderToken = $sender->createToken('mobile')->plainTextToken;
        $recipientToken = $recipient->createToken('mobile')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$senderToken)
            ->postJson('/api/v1/public/dm/', [
                'recipient_id' => $recipient->id,
                'message' => 'Bonjour avec notification',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('push_notifications', [
            'user_id' => $recipient->id,
            'actor_id' => $sender->id,
            'type' => 'chat.dm',
            'status' => 'queued',
        ]);

        $this->forgetAuthGuards();

        $notificationId = PushNotification::query()->where('user_id', $recipient->id)->value('id');

        $this
            ->withHeader('Authorization', 'Bearer '.$recipientToken)
            ->getJson('/api/v1/notifications/')
            ->assertOk()
            ->assertJsonPath('unreadCount', 1)
            ->assertJsonPath('notifications.0.type', 'chat.dm');

        $this->forgetAuthGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$recipientToken)
            ->postJson('/api/v1/notifications/'.$notificationId.'/read/')
            ->assertOk()
            ->assertJsonPath('notification.status', 'read');
    }

    public function test_direct_message_sends_firebase_push_when_recipient_has_device_token(): void
    {
        config()->set('services.firebase.project_id', 'firebase-test');
        config()->set('services.firebase.credentials', __FILE__);

        $sender = User::factory()->create(['name' => 'Sender']);
        $recipient = User::factory()->create(['name' => 'Recipient']);
        $senderToken = $sender->createToken('mobile')->plainTextToken;

        $recipient->deviceTokens()->create([
            'token' => 'valid-fcm-token',
            'token_hash' => hash('sha256', 'valid-fcm-token'),
            'platform' => 'android',
            'last_used_at' => now(),
        ]);

        $this->mock(Messaging::class, function ($mock): void {
            $mock
                ->shouldReceive('sendMulticast')
                ->once()
                ->withArgs(fn ($message, array $tokens): bool => $tokens === ['valid-fcm-token'])
                ->andReturn(MulticastSendReport::withItems([
                    SendReport::success(
                        MessageTarget::with(MessageTarget::TOKEN, 'valid-fcm-token'),
                        ['name' => 'projects/firebase-test/messages/1'],
                    ),
                ]));
        });

        $this
            ->withHeader('Authorization', 'Bearer '.$senderToken)
            ->postJson('/api/v1/public/dm/', [
                'recipient_id' => $recipient->id,
                'message' => 'Bonjour avec Firebase',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('push_notifications', [
            'user_id' => $recipient->id,
            'actor_id' => $sender->id,
            'type' => 'chat.dm',
            'status' => 'sent',
        ]);
    }

    public function test_realtime_config_and_call_token_are_available(): void
    {
        config()->set('services.livekit.url', null);
        config()->set('services.livekit.api_key', null);
        config()->set('services.livekit.api_secret', null);

        $caller = User::factory()->create();
        $receiver = User::factory()->create();
        $callerToken = $caller->createToken('mobile')->plainTextToken;

        $call = $this
            ->withHeader('Authorization', 'Bearer '.$callerToken)
            ->postJson('/api/v1/public/dm/calls/', [
                'recipient_id' => $receiver->id,
                'title' => 'Appel test',
            ])
            ->assertCreated()
            ->json('call');

        $this
            ->withHeader('Authorization', 'Bearer '.$callerToken)
            ->getJson('/api/v1/realtime/config/')
            ->assertOk()
            ->assertJsonPath('data.broadcasting.driver', 'log')
            ->assertJsonPath('data.livekit.configured', false);

        $this
            ->withHeader('Authorization', 'Bearer '.$callerToken)
            ->postJson('/api/v1/realtime/calls/'.$call['id'].'/token/')
            ->assertOk()
            ->assertJsonPath('data.provider', 'livekit')
            ->assertJsonPath('data.roomName', $call['roomName']);
    }

    public function test_realtime_config_exposes_reverb_public_settings_without_secret(): void
    {
        config()->set('broadcasting.default', 'reverb');
        config()->set('broadcasting.connections.reverb.key', 'public-reverb-key');
        config()->set('broadcasting.connections.reverb.secret', 'private-reverb-secret');
        config()->set('broadcasting.connections.reverb.options.host', 'localhost');
        config()->set('broadcasting.connections.reverb.options.port', 8080);
        config()->set('broadcasting.connections.reverb.options.scheme', 'http');

        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/realtime/config/')
            ->assertOk()
            ->assertJsonPath('data.broadcasting.driver', 'reverb')
            ->assertJsonPath('data.broadcasting.reverb.appKey', 'public-reverb-key')
            ->assertJsonPath('data.broadcasting.reverb.wsUrl', 'ws://localhost:8080/app/public-reverb-key');

        $this->assertStringNotContainsString('private-reverb-secret', $response->getContent());
    }

    public function test_livekit_token_is_generated_when_provider_is_configured(): void
    {
        config()->set('services.livekit.url', 'wss://livekit.test');
        config()->set('services.livekit.api_key', 'test-api-key');
        config()->set('services.livekit.api_secret', 'test-api-secret');
        config()->set('services.livekit.token_ttl', 3600);

        $caller = User::factory()->create(['name' => 'Caller LiveKit']);
        $receiver = User::factory()->create();
        $callerToken = $caller->createToken('mobile')->plainTextToken;

        $call = $this
            ->withHeader('Authorization', 'Bearer '.$callerToken)
            ->postJson('/api/v1/public/dm/calls/', [
                'recipient_id' => $receiver->id,
                'title' => 'Appel LiveKit test',
            ])
            ->assertCreated()
            ->json('call');

        $token = $this
            ->withHeader('Authorization', 'Bearer '.$callerToken)
            ->postJson('/api/v1/realtime/calls/'.$call['id'].'/token/')
            ->assertOk()
            ->assertJsonPath('data.provider', 'livekit')
            ->assertJsonPath('data.configured', true)
            ->assertJsonPath('data.url', 'wss://livekit.test')
            ->assertJsonPath('data.roomName', $call['roomName'])
            ->json('data.token');

        $segments = explode('.', $token);

        $this->assertCount(3, $segments);
        $this->assertSame('test-api-key', $this->decodeJwtSegment($segments[1])['iss']);
        $this->assertSame((string) $caller->id, $this->decodeJwtSegment($segments[1])['sub']);
        $this->assertSame($call['roomName'], $this->decodeJwtSegment($segments[1])['video']['room']);
    }

    public function test_auth_profile_exposes_roles_and_permissions(): void
    {
        $this->seed(PermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin');
        $token = $user->createToken('mobile')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me/')
            ->assertOk()
            ->assertJsonPath('user.roles.0', 'admin')
            ->assertJsonPath('user.permissions.0', 'access admin');
    }

    private function forgetAuthGuards(): void
    {
        $this->app['auth']->forgetGuards();
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtSegment(string $segment): array
    {
        $json = base64_decode(strtr($segment, '-_', '+/'), true);

        $this->assertIsString($json);

        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }
}
