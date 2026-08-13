<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'slug'))
                $table->string('slug')->nullable()->after('images');

            if (!Schema::hasColumn('items', 'item_type'))
                $table->enum('item_type', ['regular', 'combo'])->default('regular')->after('slug');

            if (!Schema::hasColumn('items', 'subcategory_id'))
                $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');

            if (!Schema::hasColumn('items', 'max_quantity'))
                $table->integer('max_quantity')->nullable()->after('min_quantity');

            if (!Schema::hasColumn('items', 'is_veg'))
                $table->boolean('is_veg')->default(false)->after('weight_or_piece');

            if (!Schema::hasColumn('items', 'is_jain'))
                $table->boolean('is_jain')->default(false)->after('is_veg');

            if (!Schema::hasColumn('items', 'spice_level'))
                $table->string('spice_level', 20)->nullable()->after('is_jain');

            if (!Schema::hasColumn('items', 'preparation_time'))
                $table->integer('preparation_time')->nullable()->after('spice_level');

            if (!Schema::hasColumn('items', 'allow_custom_notes'))
                $table->boolean('allow_custom_notes')->default(true)->after('preparation_time');

            if (!Schema::hasColumn('items', 'is_featured'))
                $table->boolean('is_featured')->default(false)->after('allow_custom_notes');

            if (!Schema::hasColumn('items', 'display_order'))
                $table->integer('display_order')->default(0)->after('is_featured');

            if (!Schema::hasColumn('items', 'badge'))
                $table->string('badge', 50)->nullable()->after('display_order');

            if (!Schema::hasColumn('items', 'video_url'))
                $table->string('video_url', 500)->nullable()->after('badge');

            if (!Schema::hasColumn('items', 'thumbnail_image'))
                $table->string('thumbnail_image')->nullable()->after('video_url');

            if (!Schema::hasColumn('items', 'sku'))
                $table->string('sku', 100)->nullable()->after('thumbnail_image');

            if (!Schema::hasColumn('items', 'hsn_code'))
                $table->string('hsn_code', 20)->nullable()->after('sku');

            if (!Schema::hasColumn('items', 'gst_percent'))
                $table->decimal('gst_percent', 5, 2)->default(0)->after('hsn_code');

            if (!Schema::hasColumn('items', 'cgst'))
                $table->decimal('cgst', 5, 2)->default(0)->after('gst_percent');

            if (!Schema::hasColumn('items', 'sgst'))
                $table->decimal('sgst', 5, 2)->default(0)->after('cgst');

            if (!Schema::hasColumn('items', 'igst'))
                $table->decimal('igst', 5, 2)->default(0)->after('sgst');

            if (!Schema::hasColumn('items', 'cess_percent'))
                $table->decimal('cess_percent', 5, 2)->default(0)->after('igst');

            if (!Schema::hasColumn('items', 'is_tax_inclusive'))
                $table->boolean('is_tax_inclusive')->default(false)->after('cess_percent');

            if (!Schema::hasColumn('items', 'track_inventory'))
                $table->boolean('track_inventory')->default(false)->after('is_tax_inclusive');

            if (!Schema::hasColumn('items', 'stock_quantity'))
                $table->integer('stock_quantity')->default(0)->after('track_inventory');

            if (!Schema::hasColumn('items', 'low_stock_alert'))
                $table->integer('low_stock_alert')->default(5)->after('stock_quantity');

            if (!Schema::hasColumn('items', 'rating_avg'))
                $table->decimal('rating_avg', 3, 2)->default(0)->after('low_stock_alert');

            if (!Schema::hasColumn('items', 'rating_count'))
                $table->integer('rating_count')->default(0)->after('rating_avg');

            if (!Schema::hasColumn('items', 'total_sold'))
                $table->integer('total_sold')->default(0)->after('rating_count');

            if (!Schema::hasColumn('items', 'deleted_at'))
                $table->softDeletes();
        });

        // Fix item_type enum — expand first to cover all possible old values, remap, then narrow
        if (Schema::hasColumn('items', 'item_type')) {
            DB::statement("ALTER TABLE items MODIFY COLUMN item_type ENUM('physical','digital','service','single','regular','combo') NOT NULL DEFAULT 'regular'");
            DB::statement("UPDATE items SET item_type = 'regular' WHERE item_type NOT IN ('regular','combo')");
            DB::statement("ALTER TABLE items MODIFY COLUMN item_type ENUM('regular','combo') NOT NULL DEFAULT 'regular'");
        }

        // Fix spice_level to varchar if it's still a tinyint from old schema
        if (Schema::hasColumn('items', 'spice_level')) {
            $type = DB::selectOne("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items' AND COLUMN_NAME = 'spice_level'");
            if ($type && in_array($type->DATA_TYPE, ['tinyint', 'int'])) {
                DB::statement("ALTER TABLE items MODIFY COLUMN spice_level VARCHAR(20) NULL DEFAULT NULL");
            }
        }
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                'slug', 'item_type', 'subcategory_id', 'max_quantity',
                'is_veg', 'is_jain', 'spice_level', 'preparation_time',
                'allow_custom_notes', 'is_featured', 'display_order', 'badge',
                'video_url', 'thumbnail_image', 'sku', 'hsn_code',
                'gst_percent', 'cgst', 'sgst', 'igst', 'cess_percent', 'is_tax_inclusive',
                'track_inventory', 'stock_quantity', 'low_stock_alert',
                'rating_avg', 'rating_count', 'total_sold', 'deleted_at',
            ], fn($col) => Schema::hasColumn('items', $col)));
        });
    }
};
