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
    Schema::create('user_addresses', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('user_id');
        $table->string('label')->nullable(); // Home, Office, Other
        $table->text('address_line');
        $table->string('city');
        $table->string('state');
        $table->string('pincode', 20);
        $table->decimal('lat', 10, 7)->nullable();
        $table->decimal('lng', 10, 7)->nullable();
        $table->boolean('is_default')->default(false);

        $table->timestamps();

        // Foreign Keys
        $table->foreign('user_id')->references('id')->on('app_users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
