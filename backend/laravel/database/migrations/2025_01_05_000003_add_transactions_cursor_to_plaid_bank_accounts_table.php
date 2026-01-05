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
        Schema::table('plaid_bank_accounts', function (Blueprint $table) {
            $table->text('transactions_cursor')->nullable()->after('sync_start_date');
            $table->boolean('initial_sync_complete')->default(false)->after('transactions_cursor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plaid_bank_accounts', function (Blueprint $table) {
            $table->dropColumn('transactions_cursor');
            $table->dropColumn('initial_sync_complete');
        });
    }
};

