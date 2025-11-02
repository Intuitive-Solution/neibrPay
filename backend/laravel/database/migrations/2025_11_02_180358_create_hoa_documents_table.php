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
        Schema::create('hoa_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('file_name'); // Original filename
            $table->string('file_path'); // Storage path
            $table->string('file_hash')->unique(); // For duplicate detection
            $table->bigInteger('file_size'); // File size in bytes
            $table->string('mime_type'); // File MIME type
            $table->text('description')->nullable(); // Optional user description
            $table->boolean('visible_to_residents')->default(false); // Toggle for resident visibility
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['tenant_id', 'deleted_at']);
            $table->index(['visible_to_residents', 'deleted_at']);
            $table->index('file_hash');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoa_documents');
    }
};
