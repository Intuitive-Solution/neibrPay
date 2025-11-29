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
        Schema::create('plaid_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('plaid_bank_account_id');
            $table->string('plaid_transaction_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('name');
            $table->string('merchant_name')->nullable();
            $table->string('category')->nullable();
            $table->json('categories')->nullable(); // Store full category array from Plaid
            $table->boolean('pending')->default(false);
            $table->text('personal_finance_category')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('plaid_bank_account_id')->references('id')->on('plaid_bank_accounts')->onDelete('cascade');
            $table->index('tenant_id');
            $table->index('plaid_bank_account_id');
            $table->index('date');
            $table->index('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plaid_transactions');
    }
};




