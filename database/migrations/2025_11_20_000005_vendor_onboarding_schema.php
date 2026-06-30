<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make required columns nullable so a draft account can be created with email only
        //    and extend status enum with draft + rejected.
        DB::statement("
            ALTER TABLE app_owner_shops
                MODIFY COLUMN full_name         VARCHAR(100)  NULL,
                MODIFY COLUMN password          VARCHAR(255)  NULL,
                MODIFY COLUMN phone_number      VARCHAR(20)   NULL,
                MODIFY COLUMN restaurant_name   VARCHAR(100)  NULL,
                MODIFY COLUMN restaurant_address TEXT         NULL,
                MODIFY COLUMN city              VARCHAR(100)  NULL,
                MODIFY COLUMN state             VARCHAR(100)  NULL,
                MODIFY COLUMN zip_code          VARCHAR(20)   NULL,
                MODIFY COLUMN country           VARCHAR(100)  NULL DEFAULT 'India',
                MODIFY COLUMN status            ENUM('draft','pending','active','inactive','blocked','rejected')
                                                NOT NULL DEFAULT 'draft'
        ");

        // 2. Onboarding progress tracker
        Schema::table('app_owner_shops', function (Blueprint $table) {
            if (!Schema::hasColumn('app_owner_shops', 'onboarding_step')) {
                // 0=email_verified 1=details_filled 2=photos_added 3=submitted
                $table->tinyInteger('onboarding_step')->default(0)->after('status');
            }
        });

        // 3. Extend shop_images with processing columns for job-based uploads
        Schema::table('shop_images', function (Blueprint $table) {
            $table->string('original_path')->nullable()->after('image_path');
            $table->string('thumb_path')->nullable()->after('original_path');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('thumb_path');
            $table->enum('processing_status', ['pending', 'processing', 'done', 'failed'])
                  ->default('done')  // existing rows treated as already processed
                  ->after('sort_order');
            $table->text('processing_error')->nullable()->after('processing_status');
        });
    }

    public function down(): void
    {
        Schema::table('shop_images', function (Blueprint $table) {
            $table->dropColumn(['original_path', 'thumb_path', 'sort_order', 'processing_status', 'processing_error']);
        });

        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->dropColumn('onboarding_step');
        });

        DB::statement("
            ALTER TABLE app_owner_shops
                MODIFY COLUMN full_name         VARCHAR(100)  NOT NULL,
                MODIFY COLUMN password          VARCHAR(255)  NOT NULL,
                MODIFY COLUMN phone_number      VARCHAR(20)   NOT NULL,
                MODIFY COLUMN restaurant_name   VARCHAR(100)  NOT NULL,
                MODIFY COLUMN restaurant_address TEXT         NOT NULL,
                MODIFY COLUMN city              VARCHAR(100)  NOT NULL,
                MODIFY COLUMN state             VARCHAR(100)  NOT NULL,
                MODIFY COLUMN zip_code          VARCHAR(20)   NOT NULL,
                MODIFY COLUMN country           VARCHAR(100)  NOT NULL,
                MODIFY COLUMN status            ENUM('pending','active','inactive','blocked')
                                                NOT NULL DEFAULT 'pending'
        ");
    }
};
