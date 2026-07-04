<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            // display text
            $table->string('title')->nullable();
            $table->string('heading')->nullable();
            $table->string('subtitle', 500)->nullable();
            $table->string('cta_label', 100)->nullable();
            // categorisation
            $table->enum('banner_type', ['hero', 'strip', 'popup', 'deals', 'category'])->default('hero');
            // media
            $table->enum('media_type', ['image', 'gif', 'video'])->default('image');
            $table->string('media_path', 500)->nullable();       // relative storage path
            $table->string('thumbnail_path', 500)->nullable();   // video poster frame
            // target / deep-link
            $table->enum('target_type', ['url', 'shop', 'category', 'item', 'none'])->default('none');
            $table->unsignedInteger('target_id')->nullable();
            $table->string('target_url', 500)->nullable();
            // schedule
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            // meta
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->unsignedInteger('click_count')->default(0);
            $table->boolean('is_sponsored')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
