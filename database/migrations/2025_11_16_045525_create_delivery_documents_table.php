<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_boy_id')->constrained('delivery_boys')->onDelete('cascade');
            $table->string('doc_type'); // e.g., 'license', 'id_card', 'vehicle_registration'
            $table->string('file_path'); // storage path
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_documents');
    }
};