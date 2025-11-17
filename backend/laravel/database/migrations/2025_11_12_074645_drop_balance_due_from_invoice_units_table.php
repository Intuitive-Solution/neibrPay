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
            $table->dropColumn('balance_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_units', function (Blueprint $table) {
            $table->decimal('balance_due', 10, 2)->default(0.00)->after('total');
        });
    }
};
