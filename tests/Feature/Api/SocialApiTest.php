<?php

namespace Tests\Feature\Api;

use App\Models\ChatMessage;
use App\Models\SocialGroup;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_chat_can_be_read_but_requires_auth_to_post(): void
    {
        $user = User::factory()->create([
            'name' => 'Marie Kindu',
            'avatar_url' => 'https://img.example.test/marie.jpg',
        ]);

        $this
            ->postJson('/api/v1/public/chat/', [
                'message' => 'Paix du Seigneur',
            ])
            ->assertUnauthorized();

        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/public/chat/', [
                'message' => 'Paix du Seigneur',
            ])
            ->assertCreated()
            ->assertJsonPath('data.text', 'Paix du Seigneur')
            ->assertJsonPath('data.sender.name', 'Marie Kindu');

        $this
            ->getJson('/api/v1/public/chat/')
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.sender.avatarUrl', 'https://img.example.test/marie.jpg');

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/public/chat/seen/', [
                'message_id' => $response->json('data.id'),
            ])
            ->assertOk()
            ->assertJsonPath('seen.lastSeenMessageId', $response->json('data.id'));
    }

    public function test_authenticated_user_can_create_group_and_send_group_message(): void
    {
        $this->seed(PermissionSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo('create social groups');
        $token = $user->createToken('mobile')->plainTextToken;

        $groupResponse = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/public/groups/', [
                'name' => 'Jeunesse',
                'description' => 'Groupe de la jeunesse.',
            ])
            ->assertCreated()
            ->assertJsonPath('group.slug', 'jeunesse')
            ->assertJsonPath('group.status', 'approved')
            ->assertJsonPath('group.isMember', true);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/public/groups/'.$groupResponse->json('group.slug').'/chat/', [
                'message' => 'Message au groupe',
            ])
            ->assertCreated()
            ->assertJsonPath('data.groupId', $groupResponse->json('group.id'))
            ->assertJsonPath('data.text', 'Message au groupe');

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/public/groups/mine/')
            ->assertOk()
            ->assertJsonPath('groups.0.slug', 'jeunesse');

        $this
            ->getJson('/api/v1/public/groups/'.$groupResponse->json('group.slug').'/chat/')
            ->assertOk()
            ->assertJsonCount(1, 'messages');
    }

    public function test_group_creation_without_permission_is_pending_and_not_public(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/public/groups/', [
                'name' => 'Groupe a approuver',
                'is_public' => true,
            ])
            ->assertAccepted()
            ->assertJsonPath('group.status', 'pending')
            ->assertJsonPath('group.isPublic', false);

        $this
            ->getJson('/api/v1/public/groups/')
            ->assertOk()
            ->assertJsonMissing(['slug' => $response->json('group.slug')]);
    }

    public function test_direct_messages_create_conversation_and_unread_counts(): void
    {
        $sender = User::factory()->create(['name' => 'Expediteur']);
        $recipient = User::factory()->create(['name' => 'Recepteur']);
        $senderToken = $sender->createToken('mobile')->plainTextToken;
        $recipientToken = $recipient->createToken('mobile')->plainTextToken;

        $messageResponse = $this
            ->withHeader('Authorization', 'Bearer '.$senderToken)
            ->postJson('/api/v1/public/dm/', [
                'recipient_id' => $recipient->id,
                'message' => 'Bonjour en prive',
            ])
            ->assertCreated()
            ->assertJsonPath('data.text', 'Bonjour en prive')
            ->assertJsonPath('conversation.participants.1.name', 'Recepteur');

        $conversationUuid = $messageResponse->json('conversation.uuid');

        $this->forgetAuthGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$recipientToken)
            ->getJson('/api/v1/public/dm/')
            ->assertOk()
            ->assertJsonPath('threads.0.uuid', $conversationUuid)
            ->assertJsonPath('threads.0.unread', 1);

        $this->forgetAuthGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$recipientToken)
            ->getJson('/api/v1/public/dm/'.$conversationUuid.'/')
            ->assertOk()
            ->assertJsonPath('messages.0.sender.name', 'Expediteur');

        $this->forgetAuthGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$recipientToken)
            ->getJson('/api/v1/public/social/unread/')
            ->assertOk()
            ->assertJsonPath('unread.dm', 1);
    }

    public function test_call_sessions_store_signals_and_state_changes(): void
    {
        $caller = User::factory()->create(['name' => 'Appelant']);
        $receiver = User::factory()->create(['name' => 'Receveur']);
        $callerToken = $caller->createToken('mobile')->plainTextToken;
        $receiverToken = $receiver->createToken('mobile')->plainTextToken;

        $callResponse = $this
            ->withHeader('Authorization', 'Bearer '.$callerToken)
            ->postJson('/api/v1/public/dm/calls/', [
                'recipient_id' => $receiver->id,
                'title' => 'Appel de priere',
            ])
            ->assertCreated()
            ->assertJsonPath('call.callType', 'dm')
            ->assertJsonPath('call.status', 'ringing');

        $callId = $callResponse->json('call.id');

        $this->forgetAuthGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$receiverToken)
            ->getJson('/api/v1/calls/')
            ->assertOk()
            ->assertJsonPath('calls.0.id', $callId);

        $this->forgetAuthGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$callerToken)
            ->postJson('/api/v1/call/signal/', [
                'call_id' => $callId,
                'signal_type' => 'offer',
                'payload' => ['sdp' => 'fake-offer'],
            ])
            ->assertCreated()
            ->assertJsonPath('signal.type', 'offer')
            ->assertJsonPath('signal.payload.sdp', 'fake-offer');

        $this->forgetAuthGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$receiverToken)
            ->postJson('/api/v1/call/state/', [
                'call_id' => $callId,
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('call.status', 'active');
    }

    public function test_private_group_chat_is_not_publicly_readable(): void
    {
        $owner = User::factory()->create();

        $group = SocialGroup::create([
            'name' => 'Intercession interne',
            'slug' => 'intercession-interne',
            'is_public' => false,
            'created_by' => $owner->id,
        ]);

        ChatMessage::create([
            'uuid' => '00000000-0000-0000-0000-000000000001',
            'scope' => 'group',
            'social_group_id' => $group->id,
            'sender_id' => $owner->id,
            'body' => 'Message interne',
            'message_type' => 'text',
        ]);

        $this
            ->getJson('/api/v1/public/groups/intercession-interne/chat/')
            ->assertForbidden();
    }

    private function forgetAuthGuards(): void
    {
        $this->app['auth']->forgetGuards();
    }
}
