<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            if (!Schema::hasColumn('app_owner_shops', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('otp_expires_at');
            }
            if (!Schema::hasColumn('app_owner_shops', 'fcm_token')) {
                $table->string('fcm_token', 500)->nullable()->after('api_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->dropColumn(['last_active_at', 'fcm_token']);
        });
    }
};
