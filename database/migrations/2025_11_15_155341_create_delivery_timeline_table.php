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
    Schema::create('delivery_timeline', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('order_id');
        $table->string('status');  
        $table->text('message')->nullable();
        $table->json('meta')->nullable();

        $table->timestamps();

        $table->foreign('order_id')
              ->references('id')->on('orders')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('delivery_timeline');
}
};
