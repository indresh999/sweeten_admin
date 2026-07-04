<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the CREATE migration's effect (it failed but let's drop the new one cleanly)
        Schema::dropIfExists('temp_banners_placeholder');

        Schema::table('banners', function (Blueprint $table) {
            // Text content fields
            if (!Schema::hasColumn('banners', 'heading'))   $table->string('heading')->nullable()->after('title');
            if (!Schema::hasColumn('banners', 'subtitle'))  $table->string('subtitle', 500)->nullable()->after('heading');
            if (!Schema::hasColumn('banners', 'cta_label')) $table->string('cta_label', 100)->nullable()->after('subtitle');

            // Media fields
            if (!Schema::hasColumn('banners', 'media_type'))      $table->enum('media_type', ['image', 'gif', 'video'])->default('image')->after('banner_type');
            if (!Schema::hasColumn('banners', 'media_path'))      $table->string('media_path', 500)->nullable()->after('media_type');
            if (!Schema::hasColumn('banners', 'thumbnail_path'))  $table->string('thumbnail_path', 500)->nullable()->after('media_path');

            // Fix status to support draft
            if (Schema::hasColumn('banners', 'status')) {
                $table->enum('status', ['active', 'inactive', 'draft'])->default('active')->change();
            }
        });

        // Drop unused legacy columns
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'created_by')) $table->dropColumn('created_by');
            if (Schema::hasColumn('banners', 'updated_by')) $table->dropColumn('updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['heading', 'subtitle', 'cta_label', 'media_type', 'media_path', 'thumbnail_path'],
                fn($col) => Schema::hasColumn('banners', $col)
            ));
        });
    }
};
