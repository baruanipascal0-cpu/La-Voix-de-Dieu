<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('collection', 80)->default('general')->index();
            $table->string('disk', 40)->default('public');
            $table->string('visibility', 20)->default('public');
            $table->text('path');
            $table->text('url');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable()->index();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'collection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_uploads');
    }
};
