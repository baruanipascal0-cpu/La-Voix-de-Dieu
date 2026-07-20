<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_groups', function (Blueprint $table): void {
            $table->string('status', 40)->default('approved')->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable()->index();
            $table->timestamp('blocked_at')->nullable()->index();
            $table->text('moderation_reason')->nullable();
        });

        Schema::table('chat_messages', function (Blueprint $table): void {
            $table->string('status', 40)->default('published')->index();
            $table->timestamp('reported_at')->nullable()->index();
            $table->unsignedBigInteger('moderated_by')->nullable()->index();
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_reason')->nullable();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('suspended_at')->nullable()->index();
            $table->timestamp('blocked_at')->nullable()->index();
            $table->text('moderation_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['suspended_at', 'blocked_at', 'moderation_reason']);
        });

        Schema::table('chat_messages', function (Blueprint $table): void {
            $table->dropColumn(['status', 'reported_at', 'moderated_by', 'moderated_at', 'moderation_reason']);
        });

        Schema::table('social_groups', function (Blueprint $table): void {
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'suspended_at', 'blocked_at', 'moderation_reason']);
        });
    }
};
