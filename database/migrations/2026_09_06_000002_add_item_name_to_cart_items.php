<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cart_items', 'item_name')) {
            DB::statement('ALTER TABLE cart_items ADD COLUMN item_name VARCHAR(255) DEFAULT NULL AFTER quantity');
        }
        if (!Schema::hasColumn('cart_items', 'variant_label')) {
            DB::statement('ALTER TABLE cart_items ADD COLUMN variant_label VARCHAR(255) DEFAULT NULL AFTER item_name');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cart_items', 'item_name')) {
            DB::statement('ALTER TABLE cart_items DROP COLUMN item_name');
        }
        if (Schema::hasColumn('cart_items', 'variant_label')) {
            DB::statement('ALTER TABLE cart_items DROP COLUMN variant_label');
        }
    }
};
