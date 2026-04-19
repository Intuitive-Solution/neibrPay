<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration predates create_plaid_bank_accounts_table; those columns are
     * defined on the create migration for fresh installs. Here we only add them
     * when an older database already has the table but is missing the columns.
     */
    public function up(): void
    {
        if (!Schema::hasTable('plaid_bank_accounts')) {
            return;
        }

        Schema::table('plaid_bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('plaid_bank_accounts', 'transactions_cursor')) {
                $table->text('transactions_cursor')->nullable()->after('sync_start_date');
            }
            if (!Schema::hasColumn('plaid_bank_accounts', 'initial_sync_complete')) {
                $table->boolean('initial_sync_complete')->default(false)->after('transactions_cursor');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('plaid_bank_accounts')) {
            return;
        }

        Schema::table('plaid_bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('plaid_bank_accounts', 'transactions_cursor')) {
                $table->dropColumn('transactions_cursor');
            }
            if (Schema::hasColumn('plaid_bank_accounts', 'initial_sync_complete')) {
                $table->dropColumn('initial_sync_complete');
            }
        });
    }
};

