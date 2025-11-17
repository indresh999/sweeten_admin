<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('delivery_boys', function (Blueprint $table) {
        if (!Schema::hasColumn('delivery_boys', 'is_verified')) {
            $table->boolean('is_verified')->default(false);
        }
        if (!Schema::hasColumn('delivery_boys', 'last_login_at')) {
            $table->timestamp('last_login_at')->nullable();
        }
        if (!Schema::hasColumn('delivery_boys', 'status')) {
            $table->string('status')->default('offline'); // offline/online/blocked
        }
    });
}

public function down()
{
    Schema::table('delivery_boys', function (Blueprint $table) {
        $table->dropColumn(['is_verified','last_login_at','status']);
    });
}
};
