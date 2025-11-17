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
    Schema::create('app_owner_shops', function (Blueprint $table) {
        $table->id('shop_id');

        $table->string('full_name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('phone_number')->unique();

        $table->string('restaurant_name');
        $table->text('restaurant_address');
        $table->string('city');
        $table->string('state');
        $table->string('zip_code');
        $table->string('country');

        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();

        $table->string('gst_number')->nullable();
        $table->string('pan_number')->nullable();

        $table->string('otp_code')->nullable();
        $table->dateTime('otp_expires_at')->nullable();

        $table->timestamps();

        $table->index(['latitude', 'longitude']);
        $table->index('city');
        $table->index('phone_number');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_owner_shops');
    }
};
