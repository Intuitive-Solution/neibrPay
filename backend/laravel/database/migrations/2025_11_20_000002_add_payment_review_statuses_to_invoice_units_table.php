<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For fresh databases, the enum already includes these values in the create migration
        // This migration is only needed for existing databases
        // Check if the column exists and modify only if needed (database-agnostic approach)
        if (Schema::hasTable('invoice_units') && Schema::hasColumn('invoice_units', 'status')) {
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                // MySQL-specific enum modification
                DB::statement("ALTER TABLE invoice_units MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled', 'in_review', 'payment_rejected') DEFAULT 'draft'");
            } elseif ($driver === 'pgsql') {
                // PostgreSQL: Check if enum values exist, add if not
                // Note: This is a simplified approach - full implementation would check existing enum values
                // For fresh databases, this will be a no-op since the enum already has the values
                try {
                    DB::statement("ALTER TYPE invoice_units_status_enum ADD VALUE IF NOT EXISTS 'in_review'");
                    DB::statement("ALTER TYPE invoice_units_status_enum ADD VALUE IF NOT EXISTS 'payment_rejected'");
                } catch (\Exception $e) {
                    // Enum values may already exist or table may use check constraint instead
                    // Silently continue - migration may not be needed for fresh databases
                }
            }
            // For SQLite and other databases, enum is typically stored as string with check constraint
            // No modification needed as the create migration already includes all values
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot safely remove enum values in PostgreSQL
        // For MySQL, revert to original enum (may fail if data exists with new values)
        if (Schema::hasTable('invoice_units') && Schema::hasColumn('invoice_units', 'status')) {
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                try {
                    DB::statement("ALTER TABLE invoice_units MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled') DEFAULT 'draft'");
                } catch (\Exception $e) {
                    // Cannot revert if data exists with new enum values
                }
            }
            // PostgreSQL: Cannot remove enum values once added
            // SQLite: No action needed
        }
    }
};

