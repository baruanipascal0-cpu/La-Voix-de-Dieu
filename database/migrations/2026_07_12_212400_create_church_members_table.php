<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('display_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('avatar_url')->nullable();
            $table->string('jurisdiction')->nullable();
            $table->string('gender')->nullable();
            $table->string('member_type')->nullable();
            $table->date('joined_at')->nullable();
            $table->boolean('show_contacts')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_members');
    }
};
