<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('call_type')->default('dm')->index();
            $table->string('status')->default('ringing')->index();
            $table->string('title')->nullable();
            $table->foreignId('initiator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('social_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('direct_conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('livekit');
            $table->string('room_name')->nullable()->index();
            $table->string('channel_name')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_state_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_sessions');
    }
};
