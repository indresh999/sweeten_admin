<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: expand enum to include both old and new values
        DB::statement("ALTER TABLE items MODIFY COLUMN item_type ENUM('physical','digital','service','regular','combo') NOT NULL DEFAULT 'physical'");
        // Step 2: remap old values to regular
        DB::statement("UPDATE items SET item_type = 'regular' WHERE item_type IN ('physical','digital','service')");
        // Step 3: narrow to just the new values
        DB::statement("ALTER TABLE items MODIFY COLUMN item_type ENUM('regular','combo') NOT NULL DEFAULT 'regular'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE items MODIFY COLUMN item_type ENUM('physical','digital','service') NOT NULL DEFAULT 'physical'");
    }
};
