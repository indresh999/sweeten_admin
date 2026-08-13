<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('doc_type', 50); // fssai_certificate, gst_certificate, pan_card, trade_licence, cancelled_cheque, other
            $table->string('doc_label', 100)->nullable(); // human label the vendor typed for 'other'
            $table->string('file_path');
            $table->string('original_name', 255)->nullable();
            $table->unsignedInteger('file_size')->nullable(); // bytes
            $table->string('mime_type', 100)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable(); // admin can add rejection note
            $table->timestamps();

            $table->foreign('shop_id')->references('shop_id')->on('app_owner_shops')->onDelete('cascade');
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_documents');
    }
};
