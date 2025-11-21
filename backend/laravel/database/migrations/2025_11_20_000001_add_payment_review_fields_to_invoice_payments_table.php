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
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Add payment status field with default 'approved' for backward compatibility
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected'])->default('approved')->after('payment_date');
            
            // Add comment fields
            $table->text('admin_comment_public')->nullable()->after('status');
            $table->text('admin_comment_private')->nullable()->after('admin_comment_public');
            
            // Add review tracking
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->after('admin_comment_private');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            
            // Add indexes
            $table->index('status');
            $table->index('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['status']);
            $table->dropIndex(['reviewed_by']);
            
            // Drop foreign key
            $table->dropForeignIdFor('User', 'reviewed_by');
            
            // Drop columns
            $table->dropColumn([
                'status',
                'admin_comment_public',
                'admin_comment_private',
                'reviewed_by',
                'reviewed_at',
            ]);
        });
    }
};

