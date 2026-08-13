<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);           // e.g. Kilogram
            $table->string('short_name', 20);     // e.g. kg
            $table->string('category', 30)->default('weight'); // weight|volume|count|length
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed common grocery units
        $units = [
            // Weight
            ['name' => 'Gram',      'short_name' => 'g',   'category' => 'weight',  'sort_order' => 1],
            ['name' => 'Kilogram',  'short_name' => 'kg',  'category' => 'weight',  'sort_order' => 2],
            ['name' => '250 Gram',  'short_name' => '250g','category' => 'weight',  'sort_order' => 3],
            ['name' => '500 Gram',  'short_name' => '500g','category' => 'weight',  'sort_order' => 4],
            // Volume
            ['name' => 'Milliliter','short_name' => 'ml',  'category' => 'volume',  'sort_order' => 5],
            ['name' => 'Liter',     'short_name' => 'L',   'category' => 'volume',  'sort_order' => 6],
            ['name' => '200 ml',    'short_name' => '200ml','category' => 'volume', 'sort_order' => 7],
            ['name' => '500 ml',    'short_name' => '500ml','category' => 'volume', 'sort_order' => 8],
            // Count / Pack
            ['name' => 'Piece',     'short_name' => 'pc',  'category' => 'count',   'sort_order' => 9],
            ['name' => 'Pack',      'short_name' => 'pack','category' => 'count',   'sort_order' => 10],
            ['name' => 'Dozen',     'short_name' => 'doz', 'category' => 'count',   'sort_order' => 11],
            ['name' => 'Box',       'short_name' => 'box', 'category' => 'count',   'sort_order' => 12],
            ['name' => 'Bunch',     'short_name' => 'bch', 'category' => 'count',   'sort_order' => 13],
            ['name' => 'Bag',       'short_name' => 'bag', 'category' => 'count',   'sort_order' => 14],
            ['name' => 'Bottle',    'short_name' => 'btl', 'category' => 'count',   'sort_order' => 15],
            ['name' => 'Packet',    'short_name' => 'pkt', 'category' => 'count',   'sort_order' => 16],
            ['name' => 'Tin',       'short_name' => 'tin', 'category' => 'count',   'sort_order' => 17],
            ['name' => 'Tray',      'short_name' => 'tray','category' => 'count',   'sort_order' => 18],
            // Serving
            ['name' => 'Serving',   'short_name' => 'srv', 'category' => 'serving', 'sort_order' => 19],
            ['name' => 'Plate',     'short_name' => 'plt', 'category' => 'serving', 'sort_order' => 20],
        ];

        $now = now();
        DB::table('product_units')->insert(
            array_map(fn($u) => array_merge($u, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]), $units)
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
