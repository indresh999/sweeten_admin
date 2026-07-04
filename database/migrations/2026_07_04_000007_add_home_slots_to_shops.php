<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->unsignedSmallInteger('featured_sort_order')->default(0)->after('is_featured');
            $table->boolean('is_popular')->default(false)->after('featured_sort_order');
            $table->unsignedSmallInteger('popular_sort_order')->default(0)->after('is_popular');
            $table->string('popular_area', 100)->nullable()->after('popular_sort_order');

            $table->index('is_featured');
            $table->index(['is_popular', 'popular_area', 'popular_sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_popular', 'popular_area', 'popular_sort_order']);
            $table->dropColumn(['is_featured', 'featured_sort_order', 'is_popular', 'popular_sort_order', 'popular_area']);
        });
    }
};
