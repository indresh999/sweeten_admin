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
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('item_id');

        $table->integer('quantity')->default(1);
        $table->decimal('price', 10, 2);
        $table->decimal('offer_price', 10, 2)->nullable();
        $table->decimal('item_total', 10, 2)->default(0);

        $table->timestamps();

        // FKs
        $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        $table->index('order_id');
$table->index('item_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
