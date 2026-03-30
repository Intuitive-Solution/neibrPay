<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_unit_id',
        'reminder_kind',
        'reminder_key',
        'status',
        'phase_day_value',
        'recipient_email',
        'cc_emails',
        'payload_hash',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'cc_emails' => 'array',
        'sent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceUnit::class, 'invoice_unit_id');
    }
}

