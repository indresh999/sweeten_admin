<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('delivery_assignments', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('delivery_boy_id')->nullable();

        $table->enum('status', [
            'assigned',
            'accepted',
            'rejected',
            'picked',
            'delivered',
            'cancelled'
        ])->default('assigned');

        $table->integer('attempts')->default(0);

        $table->dateTime('expected_delivery')->nullable();
        $table->dateTime('accepted_at')->nullable();
        $table->dateTime('rejected_at')->nullable();
        $table->dateTime('picked_at')->nullable();
        $table->dateTime('delivered_at')->nullable();

        $table->timestamps();

        $table->foreign('order_id')
              ->references('id')->on('orders')
              ->onDelete('cascade');

        $table->foreign('delivery_boy_id')
              ->references('id')->on('delivery_boys')
              ->onDelete('set null');
    });
}

public function down(): void
{
    Schema::dropIfExists('delivery_assignments');
}
};
