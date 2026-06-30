<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->enum('file_type', ['image', 'video'])->default('image');
            $table->string('original_path');       // raw uploaded file
            $table->string('processed_path')->nullable(); // resized/optimised
            $table->string('thumb_path')->nullable();     // 300×300 thumb
            $table->string('mime_type', 80)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_thumbnail')->default(false);
            $table->enum('processing_status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->text('processing_error')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->index(['item_id', 'sort_order']);
            $table->index(['item_id', 'processing_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_media');
    }
};
