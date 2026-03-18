<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'zelle' to payment_method ENUM (validation already allows it in InvoicePaymentController).
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'stripe_card', 'stripe_ach', 'zelle', 'other') DEFAULT 'other'");
        } elseif ($driver === 'pgsql') {
            try {
                DB::statement("ALTER TYPE invoice_payments_payment_method_enum ADD VALUE IF NOT EXISTS 'zelle'");
            } catch (\Exception $e) {
                // Enum value may already exist
            }
        }
        // SQLite stores as string; no change needed if validation allows it
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Revert to enum without zelle (fails if any row has payment_method = 'zelle')
            try {
                DB::statement("ALTER TABLE invoice_payments MODIFY COLUMN payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'stripe_card', 'stripe_ach', 'other') DEFAULT 'other'");
            } catch (\Exception $e) {
                // Cannot revert if data exists with payment_method = 'zelle'
            }
        }
        // PostgreSQL: cannot remove enum values once added
    }
};
