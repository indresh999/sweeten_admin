<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_boys', 'email')) {
                $table->string('email')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('delivery_boys', 'otp')) {
                $table->string('otp')->nullable()->after('email');
            }
            if (!Schema::hasColumn('delivery_boys', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn(['email', 'otp', 'otp_expires_at']);
        });
    }
};
