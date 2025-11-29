<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove unique constraint from plaid_item_id to allow multiple accounts per Plaid Item
     */
    public function up(): void
    {
        Schema::table('plaid_bank_accounts', function (Blueprint $table) {
            // Drop the unique constraint on plaid_item_id
            // Multiple accounts can belong to the same Plaid Item
            $table->dropUnique(['plaid_item_id']);
            
            // Add a regular index for efficient querying
            $table->index('plaid_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plaid_bank_accounts', function (Blueprint $table) {
            // Remove the index
            $table->dropIndex(['plaid_item_id']);
            
            // Restore the unique constraint (may fail if duplicate item_ids exist)
            $table->unique('plaid_item_id');
        });
    }
};
