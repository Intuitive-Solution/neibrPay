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
            // Add Firebase UID field
            if (!Schema::hasColumn('users', 'firebase_uid')) {
                $table->string('firebase_uid')->nullable()->unique()->after('email');
            }
            
            // Add tenant relationship
            if (!Schema::hasColumn('users', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('firebase_uid');
            }
            
            // Add role field
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('admin')->after('tenant_id');
            }
            
            // Add phone number field
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('role');
            }
            
            // Add avatar URL field
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('phone_number');
            }
            
            // Add active status field
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('avatar_url');
            }
            
            // Add last login timestamp
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
        });
        
        // Add indexes and foreign key constraints
        Schema::table('users', function (Blueprint $table) {
            // Add indexes
            if (!Schema::hasColumn('users', 'firebase_uid_index')) {
                $table->index('firebase_uid');
            }
            if (!Schema::hasColumn('users', 'tenant_id_index')) {
                $table->index('tenant_id');
            }
            if (!Schema::hasColumn('users', 'role_index')) {
                $table->index('role');
            }
            if (!Schema::hasColumn('users', 'is_active_index')) {
                $table->index('is_active');
            }
            
            // Add foreign key constraint
            if (!Schema::hasColumn('users', 'tenant_id_foreign')) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraint first
            if (Schema::hasColumn('users', 'tenant_id_foreign')) {
                $table->dropForeign(['tenant_id']);
            }
            
            // Drop indexes
            if (Schema::hasColumn('users', 'firebase_uid_index')) {
                $table->dropIndex(['firebase_uid']);
            }
            if (Schema::hasColumn('users', 'tenant_id_index')) {
                $table->dropIndex(['tenant_id']);
            }
            if (Schema::hasColumn('users', 'role_index')) {
                $table->dropIndex(['role']);
            }
            if (Schema::hasColumn('users', 'is_active_index')) {
                $table->dropIndex(['is_active']);
            }
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Drop columns in reverse order
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'avatar_url')) {
                $table->dropColumn('avatar_url');
            }
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->dropColumn('phone_number');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
            if (Schema::hasColumn('users', 'firebase_uid')) {
                $table->dropColumn('firebase_uid');
            }
        });
    }
};
