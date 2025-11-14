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
            // Add PayPal-related fields
            $table->string('paypal_order_id')->nullable()->after('stripe_payment_method');
            $table->string('paypal_payment_id')->nullable()->after('paypal_order_id');
            $table->enum('paypal_payment_method', ['paypal_balance', 'card', 'bank_account'])->nullable()->after('paypal_payment_id');
            
            // Add indexes for PayPal fields
            $table->index('paypal_order_id');
            $table->index('paypal_payment_id');
        });

        // Modify payment_method enum to include PayPal payment methods
        // Using DBAL to modify enum column
        DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'stripe_card', 'stripe_ach', 'paypal_balance', 'paypal_card', 'paypal_bank_account', 'other') DEFAULT 'other'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['paypal_order_id']);
            $table->dropIndex(['paypal_payment_id']);
            
            // Remove PayPal fields
            $table->dropColumn([
                'paypal_order_id',
                'paypal_payment_id',
                'paypal_payment_method',
            ]);
        });

        // Revert payment_method enum to exclude PayPal values
        DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'stripe_card', 'stripe_ach', 'other') DEFAULT 'other'");
    }
};
