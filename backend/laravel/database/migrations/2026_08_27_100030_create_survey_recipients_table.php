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
        Schema::create('survey_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            // Surveys target units only - voting is per unit, not per user
            $table->enum('recipient_type', ['all_units', 'unit']);
            // recipient_id references units when recipient_type is 'unit', null for 'all_units'
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->timestamps();

            $table->index('survey_id');
            $table->index('recipient_type');
            $table->index('recipient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_recipients');
    }
};
