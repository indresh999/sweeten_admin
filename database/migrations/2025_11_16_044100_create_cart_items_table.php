<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            // FK to users table
            $table->unsignedBigInteger('user_id');

            // FK to app_owner_shops (owner_id links to shop_id)
            $table->unsignedBigInteger('owner_id');

            // FK to items table
            $table->unsignedBigInteger('item_id');

            // quantity
            $table->integer('quantity')->default(1);

            // store price snapshot at time of adding
            $table->integer('price')->nullable();
            $table->integer('offer_price')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('owner_id')->references('shop_id')->on('app_owner_shops')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};