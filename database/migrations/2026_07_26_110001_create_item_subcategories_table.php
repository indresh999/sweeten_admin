<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('item_subcategories')) return;

        Schema::create('item_subcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('hsn_code', 20)->nullable();
            $table->string('sac_code', 20)->nullable();
            $table->decimal('cgst_percent', 5, 2)->default(0);
            $table->decimal('sgst_percent', 5, 2)->default(0);
            $table->decimal('igst_percent', 5, 2)->default(0);
            $table->decimal('cess_percent', 5, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->boolean('is_tax_inclusive')->default(false);
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->string('commission_type', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('item_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_subcategories');
    }
};
