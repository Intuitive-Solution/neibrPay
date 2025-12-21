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
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->enum('stripe_payment_method', ['card', 'ach_debit'])->nullable();
            
            // Add indexes for Stripe fields
            $table->index('stripe_checkout_session_id');
            $table->index('stripe_payment_intent_id');
        });

        // For fresh databases, the enum already includes these values in the create migration
        // This modification is only needed for existing databases
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL-specific enum modification
            DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'stripe_card', 'stripe_ach', 'other') DEFAULT 'other'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Check if enum values exist, add if not
            try {
                DB::statement("ALTER TYPE invoice_payments_payment_method_enum ADD VALUE IF NOT EXISTS 'stripe_card'");
                DB::statement("ALTER TYPE invoice_payments_payment_method_enum ADD VALUE IF NOT EXISTS 'stripe_ach'");
            } catch (\Exception $e) {
                // Enum values may already exist or table may use check constraint instead
                // Silently continue - migration may not be needed for fresh databases
            }
        }
        // For SQLite and other databases, enum is typically stored as string with check constraint
        // No modification needed as the create migration already includes all values
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

        // Cannot safely remove enum values in PostgreSQL
        // For MySQL, revert to original enum (may fail if data exists with new values)
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'other') DEFAULT 'other'");
            } catch (\Exception $e) {
                // Cannot revert if data exists with new enum values
            }
        }
        // PostgreSQL: Cannot remove enum values once added
        // SQLite: No action needed
    }
};
