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
        Schema::create('invoice_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_unit_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['public_notes', 'private_notes', 'terms', 'footer']);
            $table->longText('content')->nullable();
            $table->timestamps();
            
            // Unique constraint to ensure one note per type per invoice
            $table->unique(['invoice_unit_id', 'type']);
            
            // Indexes
            $table->index('invoice_unit_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_notes');
    }
};