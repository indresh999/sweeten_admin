<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_combo_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('combo_id');    // FK → items.id where item_type='combo'
            $table->unsignedBigInteger('item_id');     // component item
            $table->unsignedBigInteger('variant_id')->nullable(); // specific variant of the component
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('display_label', 120)->nullable(); // optional override label
            $table->timestamps();

            $table->foreign('combo_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->index('combo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_combo_components');
    }
};
