<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_images', function (Blueprint $table) {
            $table->string('original_path')->nullable()->after('image_path');
            $table->string('thumb_path')->nullable()->after('original_path');
            $table->integer('sort_order')->default(0)->after('thumb_path');
            $table->string('processing_status')->default('pending')->after('sort_order');
            $table->text('processing_error')->nullable()->after('processing_status');
        });
    }

    public function down(): void
    {
        Schema::table('shop_images', function (Blueprint $table) {
            $table->dropColumn([
                'original_path', 'thumb_path', 'sort_order',
                'processing_status', 'processing_error',
            ]);
        });
    }
};
