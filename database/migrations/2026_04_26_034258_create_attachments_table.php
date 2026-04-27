<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->string('original_name', 255);
            $table->string('stored_path', 2048);
            $table->string('mime_type', 191);
            $table->string('extension', 10);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
