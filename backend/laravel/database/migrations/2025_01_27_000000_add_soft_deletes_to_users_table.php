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
        Schema::table('users', function (Blueprint $table) {
            // Add soft delete column
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Add index for soft delete performance
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_deleted_at_index')) {
                $table->index('deleted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop index first
            if (Schema::hasIndex('users', 'users_deleted_at_index')) {
                $table->dropIndex(['deleted_at']);
            }
            
            // Drop soft delete column
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
