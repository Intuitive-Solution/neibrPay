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
        Schema::create('plaid_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('plaid_item_id'); // Not unique - multiple accounts can belong to same Plaid Item
            $table->text('plaid_access_token'); // Encrypted
            $table->string('institution_id');
            $table->string('institution_name');
            $table->string('account_id')->unique();
            $table->string('account_name');
            $table->string('account_mask'); // Last 4 digits
            $table->decimal('current_balance', 12, 2)->nullable();
            $table->decimal('available_balance', 12, 2)->nullable();
            $table->date('sync_start_date')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('status', ['active', 'error', 'disconnected'])->default('active');
            $table->string('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
            $table->index('plaid_item_id'); // Regular index, not unique
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plaid_bank_accounts');
    }
};




