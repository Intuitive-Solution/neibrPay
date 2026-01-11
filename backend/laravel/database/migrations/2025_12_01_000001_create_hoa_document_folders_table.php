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
        Schema::create('hoa_document_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('hoa_document_folders')->onDelete('cascade');
            $table->boolean('visible_to_residents')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['tenant_id', 'deleted_at']);
            $table->index(['parent_id', 'deleted_at']);
            $table->index(['visible_to_residents', 'deleted_at']);
            
            // Unique constraint: folder name must be unique per tenant per parent
            $table->unique(['tenant_id', 'parent_id', 'name', 'deleted_at'], 'unique_folder_name_per_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoa_document_folders');
    }
};


