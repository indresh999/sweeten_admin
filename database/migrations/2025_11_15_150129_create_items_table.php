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
    Schema::create('items', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('owner_id');   // shop_id
        $table->unsignedBigInteger('category_id')->nullable();

        $table->string('item_name');
        $table->text('description')->nullable();

        $table->decimal('price', 10, 2);
        $table->decimal('offer_price', 10, 2)->nullable();

        $table->integer('min_quantity')->default(1);
        $table->string('weight_or_piece')->nullable(); // 1kg, 500gm, piece

        $table->enum('status', ['active', 'inactive'])->default('active');

        $table->json('images')->nullable();

        $table->timestamps();

        // Foreign Keys
        $table->foreign('owner_id')->references('shop_id')->on('app_owner_shops')->onDelete('cascade');
        $table->foreign('category_id')->references('id')->on('item_categories')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
