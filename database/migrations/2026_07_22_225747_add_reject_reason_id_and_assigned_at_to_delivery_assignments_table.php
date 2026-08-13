<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('reject_reason_id')->nullable()->after('status');
            $table->text('reject_remark')->nullable()->after('reject_reason_id');
            $table->dateTime('assigned_at')->nullable()->after('expected_delivery');
            $table->foreign('reject_reason_id')->references('id')->on('delivery_reject_reasons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_assignments', function (Blueprint $table) {
            $table->dropForeign(['reject_reason_id']);
            $table->dropColumn(['reject_reason_id', 'reject_remark', 'assigned_at']);
        });
    }
};
