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
        Schema::create('invoice_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_unit_id')->constrained()->onDelete('cascade');
            $table->date('next_due_date');
            $table->integer('remaining_cycles')->nullable(); // NULL for endless
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_unit_id');
            $table->index('next_due_date');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_schedules');
    }
};