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
        Schema::create('invoice_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->string('po_number')->nullable();
            $table->enum('frequency', ['one-time', 'monthly', 'weekly', 'quarterly', 'yearly']);
            $table->date('start_date');
            $table->string('remaining_cycles', 20)->nullable(); // 'endless' or number as string
            $table->enum('due_date', ['use_payment_terms', 'net_15', 'net_30', 'net_45', 'net_60', 'due_on_receipt'])->default('use_payment_terms');
            // Early payment discount fields
            $table->boolean('early_payment_discount_enabled')->default(false)->after('due_date');
            $table->decimal('early_payment_discount_amount', 10, 2)->nullable()->after('early_payment_discount_enabled');
            $table->enum('early_payment_discount_type', ['amount', 'percentage'])->nullable()->after('early_payment_discount_amount');
            $table->date('early_payment_discount_by_date')->nullable()->after('early_payment_discount_type');
            // Late fee fields
            $table->boolean('late_fee_enabled')->default(false)->after('early_payment_discount_by_date');
            $table->decimal('late_fee_amount', 10, 2)->nullable()->after('late_fee_enabled');
            $table->enum('late_fee_type', ['amount', 'percentage'])->nullable()->after('late_fee_amount');
            $table->date('late_fee_applies_on_date')->nullable()->after('late_fee_type');
            $table->json('items'); // Flexible JSON structure for invoice items
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('status', ['draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled'])->default('draft');
            $table->foreignId('parent_invoice_id')->nullable()->constrained('invoice_units')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'unit_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['unit_id', 'status']);
            $table->index('invoice_number');
            $table->index('due_date');
            $table->index('parent_invoice_id');
            $table->index('created_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_units');
    }
};
