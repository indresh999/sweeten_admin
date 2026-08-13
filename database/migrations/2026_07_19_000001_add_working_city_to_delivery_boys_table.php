<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_boys', 'working_city')) {
                $table->string('working_city', 100)->nullable()->after('vehicle_type');
            }
            if (!Schema::hasColumn('delivery_boys', 'working_city_lat')) {
                $table->decimal('working_city_lat', 10, 7)->nullable()->after('working_city');
            }
            if (!Schema::hasColumn('delivery_boys', 'working_city_lng')) {
                $table->decimal('working_city_lng', 10, 7)->nullable()->after('working_city_lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn(['working_city', 'working_city_lat', 'working_city_lng']);
        });
    }
};
