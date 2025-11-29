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
            $table->decimal('current_balance', 12, 2)->nullable()->after('account_mask');
            $table->decimal('available_balance', 12, 2)->nullable()->after('current_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plaid_bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['current_balance', 'available_balance']);
        });
    }
};
