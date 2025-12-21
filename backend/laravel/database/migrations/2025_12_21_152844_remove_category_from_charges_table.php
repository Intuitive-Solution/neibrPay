<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if tables exist before proceeding
        if (!Schema::hasTable('charges')) {
            return;
        }

        // First, ensure all charges have a budget_category_id
        // For any charges without one, we need to create or assign a default income category
        try {
            $chargesWithoutCategory = DB::table('charges')
                ->whereNull('budget_category_id')
                ->exists();
            
            if ($chargesWithoutCategory && Schema::hasTable('budget_categories') && Schema::hasTable('users')) {
                // Get or create a default income category for each tenant
                $tenantsWithCharges = DB::table('charges')
                    ->whereNull('budget_category_id')
                    ->distinct()
                    ->pluck('tenant_id');
                
                foreach ($tenantsWithCharges as $tenantId) {
                    // Try to find an existing income category
                    $incomeCategory = DB::table('budget_categories')
                        ->where('tenant_id', $tenantId)
                        ->where('type', 'income')
                        ->first();
                    
                    if (!$incomeCategory) {
                        // Get a user ID for created_by, or use null if no users exist
                        $userId = DB::table('users')
                            ->where('tenant_id', $tenantId)
                            ->value('id');
                        
                        // Create a default income category
                        $categoryId = DB::table('budget_categories')->insertGetId([
                            'tenant_id' => $tenantId,
                            'name' => 'HOA Fees',
                            'type' => 'income',
                            'display_order' => 1,
                            'created_by' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $categoryId = $incomeCategory->id;
                    }
                    
                    // Update charges for this tenant
                    DB::table('charges')
                        ->where('tenant_id', $tenantId)
                        ->whereNull('budget_category_id')
                        ->update(['budget_category_id' => $categoryId]);
                }
            }
        } catch (\Exception $e) {
            // If data migration fails, log but continue with schema changes
            // This allows the migration to proceed on fresh databases
            \Log::warning('Failed to migrate charge category data: ' . $e->getMessage());
        }
        
        // Drop the category column and indexes if they still exist
        if (Schema::hasColumn('charges', 'category')) {
            Schema::table('charges', function (Blueprint $table) {
                // Try to drop indexes if they exist
                try {
                    $table->dropIndex(['category']);
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                try {
                    $table->dropIndex(['tenant_id', 'category']);
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                
                // Drop the category column
                $table->dropColumn('category');
            });
        }
        
        // Drop the existing foreign key constraint if it exists
        if (Schema::hasColumn('charges', 'budget_category_id')) {
            try {
                Schema::table('charges', function (Blueprint $table) {
                    $table->dropForeign(['budget_category_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Modify the column to be NOT NULL only if there are no null values
            try {
                $hasNullValues = DB::table('charges')
                    ->whereNull('budget_category_id')
                    ->exists();
                
                if (!$hasNullValues) {
                    Schema::table('charges', function (Blueprint $table) {
                        $table->unsignedBigInteger('budget_category_id')->nullable(false)->change();
                    });
                }
            } catch (\Exception $e) {
                // Column modification might fail, log and continue
                \Log::warning('Failed to modify budget_category_id column: ' . $e->getMessage());
            }
            
            // Re-add the foreign key constraint with RESTRICT (prevent deletion if charges exist)
            try {
                Schema::table('charges', function (Blueprint $table) {
                    $table->foreign('budget_category_id')
                        ->references('id')
                        ->on('budget_categories')
                        ->onDelete('restrict');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist or table might not exist, log and continue
                \Log::warning('Failed to add foreign key constraint: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the restrict foreign key constraint
        Schema::table('charges', function (Blueprint $table) {
            $table->dropForeign(['budget_category_id']);
        });
        
        // Make the column nullable again
        Schema::table('charges', function (Blueprint $table) {
            $table->unsignedBigInteger('budget_category_id')->nullable()->change();
        });
        
        // Re-add the foreign key with SET NULL behavior
        Schema::table('charges', function (Blueprint $table) {
            $table->foreign('budget_category_id')
                ->references('id')
                ->on('budget_categories')
                ->onDelete('set null');
        });
        
        Schema::table('charges', function (Blueprint $table) {
            // Re-add the category column
            $table->enum('category', ['hoa_fee', 'maintenance', 'penalties', 'special_assessment', 'other'])
                ->default('other')
                ->after('amount');
            
            // Re-add indexes
            $table->index(['category']);
            $table->index(['tenant_id', 'category']);
        });
    }
};
