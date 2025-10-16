<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the new enum values
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table with the updated enum
            DB::statement("
                CREATE TABLE invoice_units_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tenant_id INTEGER NOT NULL,
                    unit_id INTEGER NOT NULL,
                    invoice_number VARCHAR(255) NOT NULL UNIQUE,
                    po_number VARCHAR(255) NULL,
                    frequency VARCHAR(255) NOT NULL,
                    start_date DATE NOT NULL,
                    remaining_cycles VARCHAR(20) NULL,
                    due_date VARCHAR(255) NOT NULL DEFAULT 'use_payment_terms',
                    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    discount_type VARCHAR(255) NOT NULL DEFAULT 'amount',
                    auto_bill VARCHAR(255) NOT NULL DEFAULT 'disabled',
                    items TEXT NOT NULL,
                    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    paid_to_date DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    balance_due DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    status VARCHAR(255) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled')),
                    parent_invoice_id INTEGER NULL,
                    created_by INTEGER NOT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    deleted_at TIMESTAMP NULL,
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
                    FOREIGN KEY (parent_invoice_id) REFERENCES invoice_units(id) ON DELETE SET NULL,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            // Copy data from old table to new table
            DB::statement("
                INSERT INTO invoice_units_new 
                SELECT * FROM invoice_units
            ");

            // Drop old table
            DB::statement("DROP TABLE invoice_units");

            // Rename new table
            DB::statement("ALTER TABLE invoice_units_new RENAME TO invoice_units");

            // Recreate indexes
            DB::statement("CREATE INDEX invoice_units_tenant_id_unit_id_index ON invoice_units(tenant_id, unit_id)");
            DB::statement("CREATE INDEX invoice_units_tenant_id_status_index ON invoice_units(tenant_id, status)");
            DB::statement("CREATE INDEX invoice_units_unit_id_status_index ON invoice_units(unit_id, status)");
            DB::statement("CREATE INDEX invoice_units_invoice_number_index ON invoice_units(invoice_number)");
            DB::statement("CREATE INDEX invoice_units_due_date_index ON invoice_units(due_date)");
            DB::statement("CREATE INDEX invoice_units_parent_invoice_id_index ON invoice_units(parent_invoice_id)");
            DB::statement("CREATE INDEX invoice_units_created_at_index ON invoice_units(created_at)");
            DB::statement("CREATE INDEX invoice_units_deleted_at_index ON invoice_units(deleted_at)");
        } else {
            // For other databases, use raw SQL to alter the enum
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TYPE invoice_units_status_enum ADD VALUE 'partial'");
            } else {
                // For MySQL
                DB::statement("ALTER TABLE invoice_units MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled') DEFAULT 'draft'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite, we need to recreate the table without 'partial'
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table without the 'partial' status
            DB::statement("
                CREATE TABLE invoice_units_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tenant_id INTEGER NOT NULL,
                    unit_id INTEGER NOT NULL,
                    invoice_number VARCHAR(255) NOT NULL UNIQUE,
                    po_number VARCHAR(255) NULL,
                    frequency VARCHAR(255) NOT NULL,
                    start_date DATE NOT NULL,
                    remaining_cycles VARCHAR(20) NULL,
                    due_date VARCHAR(255) NOT NULL DEFAULT 'use_payment_terms',
                    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    discount_type VARCHAR(255) NOT NULL DEFAULT 'amount',
                    auto_bill VARCHAR(255) NOT NULL DEFAULT 'disabled',
                    items TEXT NOT NULL,
                    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    paid_to_date DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    balance_due DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    status VARCHAR(255) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'paid', 'overdue', 'cancelled')),
                    parent_invoice_id INTEGER NULL,
                    created_by INTEGER NOT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    deleted_at TIMESTAMP NULL,
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
                    FOREIGN KEY (parent_invoice_id) REFERENCES invoice_units(id) ON DELETE SET NULL,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            // Copy data from current table to old table, converting 'partial' to 'sent'
            DB::statement("
                INSERT INTO invoice_units_old 
                SELECT 
                    id, tenant_id, unit_id, invoice_number, po_number, frequency, start_date, 
                    remaining_cycles, due_date, discount_amount, discount_type, auto_bill, 
                    items, subtotal, tax_rate, tax_amount, total, paid_to_date, balance_due,
                    CASE WHEN status = 'partial' THEN 'sent' ELSE status END as status,
                    parent_invoice_id, created_by, created_at, updated_at, deleted_at
                FROM invoice_units
            ");

            // Drop current table
            DB::statement("DROP TABLE invoice_units");

            // Rename old table
            DB::statement("ALTER TABLE invoice_units_old RENAME TO invoice_units");

            // Recreate indexes
            DB::statement("CREATE INDEX invoice_units_tenant_id_unit_id_index ON invoice_units(tenant_id, unit_id)");
            DB::statement("CREATE INDEX invoice_units_tenant_id_status_index ON invoice_units(tenant_id, status)");
            DB::statement("CREATE INDEX invoice_units_unit_id_status_index ON invoice_units(unit_id, status)");
            DB::statement("CREATE INDEX invoice_units_invoice_number_index ON invoice_units(invoice_number)");
            DB::statement("CREATE INDEX invoice_units_due_date_index ON invoice_units(due_date)");
            DB::statement("CREATE INDEX invoice_units_parent_invoice_id_index ON invoice_units(parent_invoice_id)");
            DB::statement("CREATE INDEX invoice_units_created_at_index ON invoice_units(created_at)");
            DB::statement("CREATE INDEX invoice_units_deleted_at_index ON invoice_units(deleted_at)");
        }
    }
};