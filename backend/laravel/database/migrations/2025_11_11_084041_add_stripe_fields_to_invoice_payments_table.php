<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Add Stripe-related fields
            $table->string('stripe_checkout_session_id')->nullable()->after('payment_reference');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            $table->enum('stripe_payment_method', ['card', 'ach_debit'])->nullable()->after('stripe_payment_intent_id');
            
            // Add indexes for Stripe fields
            $table->index('stripe_checkout_session_id');
            $table->index('stripe_payment_intent_id');
        });

        // Modify payment_method enum to include Stripe payment methods
        // Using DBAL to modify enum column
        DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'stripe_card', 'stripe_ach', 'other') DEFAULT 'other'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            
            // Remove Stripe fields
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_payment_method',
            ]);
        });

        // Revert payment_method enum to original values
        DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'other') DEFAULT 'other'");
    }
};
