<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Restore table from backup if it was lost from a prior failed migration attempt
        if (!Schema::hasTable('unit_owners') && Schema::hasTable('unit_owners_backup')) {
            Schema::create('unit_owners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('unit_id')->constrained()->onDelete('cascade');
                $table->foreignId('resident_id')->constrained('users')->onDelete('cascade');
                $table->enum('type', ['owner', 'tenant', 'property_manager'])->default('owner');
                $table->timestamps();
                $table->unique(['unit_id', 'resident_id']);
                $table->index('unit_id');
                $table->index('resident_id');
            });

            DB::statement("INSERT INTO unit_owners (id, unit_id, resident_id, type, created_at, updated_at) SELECT id, unit_id, resident_id, type, created_at, updated_at FROM unit_owners_backup");
            Schema::dropIfExists('unit_owners_backup');
        } else {
            DB::statement("ALTER TABLE unit_owners MODIFY COLUMN type ENUM('owner', 'tenant', 'property_manager') NOT NULL DEFAULT 'owner'");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE unit_owners SET type = 'owner' WHERE type = 'property_manager'");
        DB::statement("ALTER TABLE unit_owners MODIFY COLUMN type ENUM('owner', 'tenant') NOT NULL DEFAULT 'owner'");
    }
};
