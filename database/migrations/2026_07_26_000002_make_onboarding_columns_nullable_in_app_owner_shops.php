<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->string('full_name')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
            $table->string('restaurant_name')->nullable()->change();
            $table->text('restaurant_address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('zip_code')->nullable()->change();
            $table->string('country')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('app_owner_shops', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->string('phone_number')->nullable(false)->change();
            $table->string('restaurant_name')->nullable(false)->change();
            $table->text('restaurant_address')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('state')->nullable(false)->change();
            $table->string('zip_code')->nullable(false)->change();
            $table->string('country')->nullable(false)->change();
        });
    }
};
