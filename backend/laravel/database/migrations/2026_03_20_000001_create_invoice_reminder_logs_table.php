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
        Schema::create('invoice_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_unit_id')->constrained('invoice_units')->onDelete('cascade');
            $table->enum('reminder_kind', ['pre_due', 'post_due']);
            $table->string('reminder_key', 64);
            $table->enum('status', ['sent', 'skipped_duplicate', 'skipped_ineligible', 'failed'])->default('sent');
            $table->integer('phase_day_value')->nullable();
            $table->string('recipient_email')->nullable();
            $table->json('cc_emails')->nullable();
            $table->string('payload_hash', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['invoice_unit_id', 'reminder_kind', 'reminder_key'], 'invoice_reminder_unique');
            $table->index(['tenant_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminder_logs');
    }
};

