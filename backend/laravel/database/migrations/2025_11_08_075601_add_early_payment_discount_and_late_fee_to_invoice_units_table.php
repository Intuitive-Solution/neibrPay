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
        Schema::table('invoice_units', function (Blueprint $table) {
            $table->boolean('early_payment_discount_enabled')->default(false)->after('discount_type');
            $table->decimal('early_payment_discount_amount', 10, 2)->nullable()->after('early_payment_discount_enabled');
            $table->enum('early_payment_discount_type', ['amount', 'percentage'])->nullable()->after('early_payment_discount_amount');
            $table->date('early_payment_discount_by_date')->nullable()->after('early_payment_discount_type');
            $table->boolean('late_fee_enabled')->default(false)->after('early_payment_discount_by_date');
            $table->decimal('late_fee_amount', 10, 2)->nullable()->after('late_fee_enabled');
            $table->date('late_fee_applies_on_date')->nullable()->after('late_fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_units', function (Blueprint $table) {
            $table->dropColumn([
                'early_payment_discount_enabled',
                'early_payment_discount_amount',
                'early_payment_discount_type',
                'early_payment_discount_by_date',
                'late_fee_enabled',
                'late_fee_amount',
                'late_fee_applies_on_date',
            ]);
        });
    }
};
