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
            $table->dropColumn(['discount_amount', 'discount_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_units', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('due_date');
            $table->enum('discount_type', ['amount', 'percentage'])->default('amount')->after('discount_amount');
        });
    }
};
