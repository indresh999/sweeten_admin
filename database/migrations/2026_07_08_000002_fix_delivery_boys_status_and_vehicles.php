<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add bank/payout columns (fcm_token already exists)
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->string('bank_account_number')->nullable()->after('fcm_token');
            $table->string('bank_ifsc')->nullable()->after('bank_account_number');
            $table->string('bank_account_name')->nullable()->after('bank_ifsc');
            $table->string('upi_id')->nullable()->after('bank_account_name');
        });

        // Add bonus/deduction to delivery_earnings
        Schema::table('delivery_earnings', function (Blueprint $table) {
            $table->decimal('bonus', 8, 2)->nullable()->default(0)->after('base_earning');
            $table->decimal('deduction', 8, 2)->nullable()->default(0)->after('bonus');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn(['bank_account_number', 'bank_ifsc', 'bank_account_name', 'upi_id']);
        });
        Schema::table('delivery_earnings', function (Blueprint $table) {
            $table->dropColumn(['bonus', 'deduction']);
        });
    }
};
