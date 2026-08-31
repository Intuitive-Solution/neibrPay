<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The FK back to survey_responses exists for integrity and vote withdrawal only.
     * Reporting queries never select unit_id/responded_by alongside survey_option_id,
     * so a tally can never be traced back to a unit.
     */
    public function up(): void
    {
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained()->onDelete('cascade');
            $table->foreignId('survey_question_id')->constrained()->onDelete('cascade');
            $table->foreignId('survey_option_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index('survey_question_id');
            $table->index('survey_option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
