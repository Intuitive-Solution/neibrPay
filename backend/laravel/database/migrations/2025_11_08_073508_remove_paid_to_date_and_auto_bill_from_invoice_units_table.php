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
            $table->dropColumn(['paid_to_date', 'auto_bill']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_units', function (Blueprint $table) {
            $table->decimal('paid_to_date', 10, 2)->default(0.00)->after('total');
            $table->enum('auto_bill', ['disabled', 'enabled', 'on_due_date', 'on_send'])->default('disabled')->after('discount_type');
        });
    }
};
