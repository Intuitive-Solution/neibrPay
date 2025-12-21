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
        Schema::table('charges', function (Blueprint $table) {
            $table->foreignId('budget_category_id')
                ->nullable()
                ->after('category')
                ->constrained('budget_categories')
                ->onDelete('set null');
            
            $table->index('budget_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropForeign(['budget_category_id']);
            $table->dropIndex(['budget_category_id']);
            $table->dropColumn('budget_category_id');
        });
    }
};

