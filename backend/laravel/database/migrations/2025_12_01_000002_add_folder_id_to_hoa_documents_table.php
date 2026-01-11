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
        Schema::table('hoa_documents', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('tenant_id')->constrained('hoa_document_folders')->onDelete('set null');
            $table->index(['folder_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoa_documents', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropIndex(['folder_id', 'deleted_at']);
            $table->dropColumn('folder_id');
        });
    }
};


