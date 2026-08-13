<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add wallet fields to delivery_boys
        if (!Schema::hasColumn('delivery_boys', 'wallet_limit')) {
            Schema::table('delivery_boys', function (Blueprint $table) {
                $table->decimal('wallet_limit', 10, 2)->default(0)->after('upi_id');
                $table->decimal('wallet_collected', 10, 2)->default(0)->after('wallet_limit');
                $table->boolean('has_pending_submission')->default(false)->after('wallet_collected');
            });
        }

        // Create cash submissions table
        if (!Schema::hasTable('delivery_cash_submissions')) {
            Schema::create('delivery_cash_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_boy_id')->constrained('delivery_boys')->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('screenshot_path');
                $table->string('status')->default('pending');
                $table->string('submission_date');
                $table->text('admin_notes')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->index(['delivery_boy_id', 'status']);
                $table->index(['delivery_boy_id', 'submission_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_cash_submissions');
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn(['wallet_limit', 'wallet_collected', 'has_pending_submission']);
        });
    }
};
