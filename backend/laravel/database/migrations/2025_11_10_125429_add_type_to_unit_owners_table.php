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
        Schema::table('unit_owners', function (Blueprint $table) {
            $table->enum('type', ['owner', 'tenant'])->default('owner')->after('resident_id');
        });
        
        // Update existing records to have 'owner' as default
        \DB::table('unit_owners')->whereNull('type')->update(['type' => 'owner']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_owners', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
