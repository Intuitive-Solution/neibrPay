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
        Schema::create('announcement_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
            $table->enum('recipient_type', ['all_members', 'all_admins', 'unit', 'resident']);
            // recipient_id can reference either users (for resident) or units (for unit)
            // For all_members/all_admins, recipient_id is null
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('announcement_id');
            $table->index('recipient_type');
            $table->index('recipient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_recipients');
    }
};
