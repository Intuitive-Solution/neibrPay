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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->string('prompt');
            // multi_select exists in the schema so unlocking it later is a UI-only change
            $table->enum('type', ['single_choice', 'multi_select', 'yes_no'])->default('single_choice');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('survey_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
