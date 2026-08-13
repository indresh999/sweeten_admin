<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->enum('payment_type', ['bank', 'upi'])->nullable()->after('pan_number');
            $table->string('upi_id')->nullable()->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'upi_id']);
        });
    }
};
