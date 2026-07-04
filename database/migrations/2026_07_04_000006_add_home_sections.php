<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed initial sections that mirror existing home_row 1 and 2
        DB::table('home_sections')->insert([
            ['id' => 1, 'title' => 'Top Categories',  'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'title' => 'More Categories', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('item_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('home_section_id')->nullable()->after('home_sort_order');
            $table->foreign('home_section_id')->references('id')->on('home_sections')->nullOnDelete();
        });

        // Migrate existing home_row (1 or 2) → home_section_id (same integer)
        DB::statement("UPDATE item_categories SET home_section_id = home_row WHERE home_row IN (1, 2)");
    }

    public function down(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropForeign(['home_section_id']);
            $table->dropColumn('home_section_id');
        });

        Schema::dropIfExists('home_sections');
    }
};
