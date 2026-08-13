<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_boys', 'onboarding_step')) {
                $table->tinyInteger('onboarding_step')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn('onboarding_step');
        });
    }
};
