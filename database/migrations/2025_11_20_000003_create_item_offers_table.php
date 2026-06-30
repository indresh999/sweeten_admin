<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Scope: null = whole shop, or limit to specific item/category
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->enum('offer_type', ['percent', 'flat', 'bogo'])->default('percent');
            $table->decimal('discount_value', 8, 2)->default(0); // % or flat ₹ amount
            $table->decimal('max_discount', 8, 2)->nullable();    // cap for percent offers
            $table->decimal('min_order_value', 8, 2)->nullable(); // minimum cart value to apply

            $table->string('badge_label', 50)->nullable(); // "FLAT 20% OFF", "BUY 1 GET 1"
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('shop_id')->references('shop_id')->on('app_owner_shops')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('item_categories')->onDelete('set null');
            $table->index(['shop_id', 'status']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_offers');
    }
};
