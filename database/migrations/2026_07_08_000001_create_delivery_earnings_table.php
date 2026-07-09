<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_boy_id')->constrained('delivery_boys')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('base_earning', 8, 2)->default(0);
            $table->decimal('bonus', 8, 2)->nullable()->default(0);
            $table->decimal('deduction', 8, 2)->nullable()->default(0);
            $table->decimal('net_earning', 8, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['delivery_boy_id', 'is_paid']);
            $table->index(['delivery_boy_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_earnings');
    }
};
