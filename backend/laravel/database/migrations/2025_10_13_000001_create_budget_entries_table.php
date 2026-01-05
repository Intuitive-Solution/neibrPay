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
        if (!Schema::hasTable('budget_entries')) {
            Schema::create('budget_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('budget_category_id')->constrained('budget_categories')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('forecast_amount', 10, 2)->default(0);
            $table->timestamps();
            
            // Unique constraint: one entry per category/year/month
            $table->unique(['budget_category_id', 'year', 'month'], 'budget_entry_unique');
            
            // Indexes
            $table->index(['tenant_id', 'year']);
            $table->index(['budget_category_id', 'year', 'month']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_entries');
    }
};

