<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_read_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scope')->default('public')->index();
            $table->foreignId('social_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('direct_conversation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('call_session_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('last_seen_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_read_receipts');
    }
};
