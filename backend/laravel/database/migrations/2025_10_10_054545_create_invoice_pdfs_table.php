<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_unit_id')->constrained()->onDelete('cascade');
            $table->integer('version')->default(1);
            $table->string('file_name'); // Original filename with invoice number
            $table->string('file_path'); // Path in storage
            $table->bigInteger('file_size'); // Size in bytes
            $table->boolean('is_latest')->default(true); // Flag for current version
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['invoice_unit_id', 'is_latest']);
            $table->index(['invoice_unit_id', 'version']);
            $table->index('generated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_pdfs');
    }
};