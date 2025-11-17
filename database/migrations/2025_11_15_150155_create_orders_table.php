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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('shop_id');

        // Financials
        $table->decimal('total_amount', 10, 2)->default(0);
        $table->decimal('gst_percent', 5, 2)->default(0);
        $table->decimal('tax_amount', 10, 2)->default(0);
        $table->decimal('delivery_charge', 10, 2)->default(0);
        $table->decimal('handling_fee', 10, 2)->default(0);
        $table->decimal('packing_fee', 10, 2)->default(0);
        $table->decimal('final_amount', 10, 2)->default(0);

        // Status
        $table->enum('status', [
            'pending',
            'confirmed',
            'preparing',
            'out_for_delivery',
            'delivered',
            'cancelled'
        ])->default('pending');

        $table->unsignedBigInteger('cancel_reason_id')->nullable();
        $table->string('cancel_remark')->nullable();

        // Address Snapshot
        $table->string('address_label')->nullable();
        $table->text('address_line')->nullable();
        $table->string('city')->nullable();
        $table->string('state')->nullable();
        $table->string('pincode', 20)->nullable();
        $table->decimal('lat', 10, 7)->nullable();
        $table->decimal('lng', 10, 7)->nullable();

        $table->timestamps();

        // Foreign Keys
        $table->foreign('user_id')->references('id')->on('app_users')->onDelete('cascade');
        $table->foreign('shop_id')->references('shop_id')->on('app_owner_shops')->onDelete('cascade');
        $table->foreign('cancel_reason_id')->references('id')->on('cancel_reasons')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
