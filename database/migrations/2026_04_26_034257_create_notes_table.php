<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('slug', 220);
            $table->longText('body');
            $table->string('thumbnail_path', 2048)->nullable();
            $table->string('reference_url', 2048)->nullable();
            $table->string('share_password_hash', 255)->nullable();
            $table->timestamp('share_expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'category_id']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
