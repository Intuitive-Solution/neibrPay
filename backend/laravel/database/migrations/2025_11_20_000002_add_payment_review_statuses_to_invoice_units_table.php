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
        // Modify invoice_units status enum to include new statuses
        DB::statement("ALTER TABLE invoice_units MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled', 'in_review', 'payment_rejected') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original status enum
        DB::statement("ALTER TABLE invoice_units MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled') DEFAULT 'draft'");
    }
};

