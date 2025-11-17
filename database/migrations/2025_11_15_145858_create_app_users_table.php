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
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone_number')->unique();
            $table->string('password')->nullable();

            $table->string('otp')->nullable();
            $table->boolean('is_verified')->default(0);

            $table->string('otp_code')->nullable();
            $table->dateTime('otp_expires_at')->nullable();

            $table->timestamps();

            $table->index('phone_number');
            $table->index('otp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
