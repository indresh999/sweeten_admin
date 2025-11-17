<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shop_id');  // FK to shops table
            $table->string('tag')->nullable();      // banner, logo, gallery, front-view etc.
            $table->string('image_path');           // file storage path

            $table->timestamps();

            $table->foreign('shop_id')
                ->references('shop_id')
                ->on('app_owner_shops')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_images');
    }
};