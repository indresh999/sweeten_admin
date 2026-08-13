<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('subcategory_id');
            $table->boolean('contains_egg')->default(false)->after('is_jain');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['unit_id', 'contains_egg']);
        });
    }
};
