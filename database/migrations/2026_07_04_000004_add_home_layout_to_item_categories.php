<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            // 0 = hidden from home, 1 = row 1, 2 = row 2
            $table->tinyInteger('home_row')->default(0)->after('is_featured');
            $table->unsignedInteger('home_sort_order')->default(0)->after('home_row');
        });

        // Migrate all currently active categories to row 1 so nothing disappears
        DB::statement("UPDATE item_categories SET home_row = 1 WHERE status = 1 OR status = 'active'");
    }

    public function down(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropColumn(['home_row', 'home_sort_order']);
        });
    }
};
