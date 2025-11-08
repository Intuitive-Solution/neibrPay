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
            $table->enum('late_fee_type', ['amount', 'percentage'])->nullable()->after('late_fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_units', function (Blueprint $table) {
            $table->dropColumn('late_fee_type');
        });
    }
};
