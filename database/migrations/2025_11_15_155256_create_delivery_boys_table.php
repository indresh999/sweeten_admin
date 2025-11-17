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
    Schema::create('delivery_boys', function (Blueprint $table) {
        $table->id();

        $table->string('full_name');
        $table->string('phone_number')->unique();
        $table->string('password')->nullable();
        $table->string('picture')->nullable();

        $table->enum('vehicle_type', ['bike', 'scooter', 'cycle', 'walking'])->default('bike');
        $table->enum('status', ['online', 'offline'])->default('offline');

        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();

        $table->integer('max_active_orders')->default(2);
        $table->integer('current_active_orders')->default(0);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_boys');
    }
};
