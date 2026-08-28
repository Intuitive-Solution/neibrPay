<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The feature shipped as "Surveys" and was renamed to "Polls".
 *
 * Tables and columns are renamed in place so any votes already cast survive.
 * MySQL 8+ carries foreign keys across both RENAME TABLE and RENAME COLUMN,
 * so no constraint has to be dropped and rebuilt here.
 */
return new class extends Migration
{
    /**
     * survey table name => poll table name
     */
    private array $tables = [
        'surveys' => 'polls',
        'survey_questions' => 'poll_questions',
        'survey_options' => 'poll_options',
        'survey_recipients' => 'poll_recipients',
        'survey_responses' => 'poll_responses',
        'survey_answers' => 'poll_answers',
    ];

    /**
     * poll table name => [survey column name => poll column name]
     */
    private array $columns = [
        'poll_questions' => ['survey_id' => 'poll_id'],
        'poll_options' => ['survey_question_id' => 'poll_question_id'],
        'poll_recipients' => ['survey_id' => 'poll_id'],
        'poll_responses' => ['survey_id' => 'poll_id'],
        'poll_answers' => [
            'survey_response_id' => 'poll_response_id',
            'survey_question_id' => 'poll_question_id',
            'survey_option_id' => 'poll_option_id',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $from => $to) {
            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }

        foreach ($this->columns as $table => $renames) {
            foreach ($renames as $from => $to) {
                if (Schema::hasColumn($table, $from) && !Schema::hasColumn($table, $to)) {
                    DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `{$from}` TO `{$to}`");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->columns as $table => $renames) {
            foreach ($renames as $from => $to) {
                if (Schema::hasColumn($table, $to) && !Schema::hasColumn($table, $from)) {
                    DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `{$to}` TO `{$from}`");
                }
            }
        }

        foreach (array_reverse($this->tables, true) as $from => $to) {
            if (Schema::hasTable($to) && !Schema::hasTable($from)) {
                Schema::rename($to, $from);
            }
        }
    }
};
