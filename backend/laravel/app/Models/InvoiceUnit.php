<?php

namespace App\Models;

use App\Support\TenantTimezone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceUnit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'unit_id',
        'invoice_number',
        'po_number',
        'frequency',
        'start_date',
        'remaining_cycles',
        'due_date',
        'early_payment_discount_enabled',
        'early_payment_discount_amount',
        'early_payment_discount_type',
        'early_payment_discount_by_date',
        'late_fee_enabled',
        'late_fee_amount',
        'late_fee_type',
        'late_fee_applies_on_date',
        'items',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'status',
        'parent_invoice_id',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'items' => 'array',
        'start_date' => 'date',
        'early_payment_discount_by_date' => 'date',
        'late_fee_applies_on_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'early_payment_discount_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'early_payment_discount_enabled' => 'boolean',
        'late_fee_enabled' => 'boolean',
    ];

    /**
     * Get the tenant that owns the invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the unit that this invoice belongs to.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who created this invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent invoice (for grouped invoices).
     */
    public function parentInvoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceUnit::class, 'parent_invoice_id');
    }

    /**
     * Get the child invoices (for grouped invoices).
     */
    public function childInvoices(): HasMany
    {
        return $this->hasMany(InvoiceUnit::class, 'parent_invoice_id');
    }

    /**
     * Get the notes for this invoice.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(InvoiceNote::class);
    }

    /**
     * Get the payments for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /**
     * Get the schedule for this invoice.
     */
    public function schedule(): HasOne
    {
        return $this->hasOne(InvoiceSchedule::class);
    }

    /**
     * Get the attachments for this invoice.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(InvoiceAttachment::class);
    }

    /**
     * Get the PDFs for this invoice.
     */
    public function pdfs(): HasMany
    {
        return $this->hasMany(InvoicePdf::class);
    }

    /**
     * Scope a query to only include invoices for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include invoices for a specific unit.
     */
    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope a query to only include invoices with a specific status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'sent')
            ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Calculate totals based on items.
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        
        if (is_array($this->items)) {
            foreach ($this->items as $item) {
                $subtotal += $item['line_total'] ?? 0;
            }
        }
        
        $this->subtotal = (float) $subtotal;
        $this->tax_amount = (float) (($subtotal * $this->tax_rate) / 100);
        $this->total = (float) ($subtotal + $this->tax_amount);
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['balance_due'];

    /**
     * Get the balance due (calculated dynamically from payments).
     * Excludes temporary Stripe payments (stripe_card/stripe_ach with null payment_intent_id).
     */
    public function getBalanceDueAttribute(): float
    {
        // Use loaded payments if available (more efficient)
        if ($this->relationLoaded('payments')) {
            // Filter out temporary Stripe payments AND only include approved payments
            // Exclude payments where payment_method is stripe_card/stripe_ach AND stripe_payment_intent_id is null
            // Also exclude payments that are not approved (in_review, rejected, pending)
            $confirmedPayments = $this->payments->filter(function ($payment) {
                // Only count approved payments
                if ($payment->status !== 'approved') {
                    return false;
                }
                // Filter out temporary Stripe payments
                return !in_array($payment->payment_method, ['stripe_card', 'stripe_ach']) 
                    || $payment->stripe_payment_intent_id !== null;
            });
            $totalPaid = $confirmedPayments->sum('amount');
        } else {
            // Fall back to query if payments aren't loaded - use confirmed scope
            $totalPaid = $this->payments()->confirmed()->sum('amount');
        }
        
        return (float) max(0, ($this->total ?? 0) - $totalPaid);
    }

    /**
     * Generate a unique invoice number for this unit.
     * Format: [UnitTitle]-[Month]-[Date]-[Year]-[SequentialNumber]
     */
    public function generateInvoiceNumber(): void
    {
        $unit = $this->unit;
        $now = now();
        $month = $now->format('m'); // Month number (01-12)
        $date = $now->format('d');  // Date (01-31)
        $year = $now->format('Y');  // Full year (2025)
        
        // Clean unit title - remove spaces and special characters, keep alphanumeric and hyphens
        $unitTitle = preg_replace('/[^a-zA-Z0-9\-]/', '', str_replace(' ', '', $unit->title));
        
        // Get the total count of invoices for this tenant and add 1
        $invoiceCount = self::where('tenant_id', $this->tenant_id)->count() + 1;
        
        // Format the sequential number with at least 4 digits (0001, 0002, etc.)
        $sequentialNumber = str_pad($invoiceCount, 4, '0', STR_PAD_LEFT);
        
        // Create the invoice number
        $invoiceNumber = sprintf(
            '%s-%s-%s-%s-%s',
            $unitTitle,
            $month,
            $date,
            $year,
            $sequentialNumber
        );
        
        // Ensure uniqueness by checking if this invoice number already exists
        // If it exists, increment the sequential number until we find a unique one
        $attempts = 0;
        $maxAttempts = 100; // Allow up to 100 attempts to find a unique number
        
        while (self::where('invoice_number', $invoiceNumber)->exists() && $attempts < $maxAttempts) {
            $invoiceCount++;
            $sequentialNumber = str_pad($invoiceCount, 4, '0', STR_PAD_LEFT);
            $invoiceNumber = sprintf(
                '%s-%s-%s-%s-%s',
                $unitTitle,
                $month,
                $date,
                $year,
                $sequentialNumber
            );
            $attempts++;
        }
        
        // If we still have a duplicate after max attempts, append timestamp as fallback
        if (self::where('invoice_number', $invoiceNumber)->exists()) {
            $timestamp = substr(str_replace('.', '', microtime(true)), -4);
            $invoiceNumber = sprintf(
                '%s-%s-%s-%s-%s',
                $unitTitle,
                $month,
                $date,
                $year,
                $timestamp
            );
        }
        
        $this->invoice_number = $invoiceNumber;
    }

    /**
     * Check if this invoice is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status !== 'sent') {
            return false;
        }

        $this->loadMissing('tenant');
        $dueDate = $this->getActualDueDate();

        return $dueDate->isPast() && $this->getBalanceDueAttribute() > 0;
    }

    /**
     * Timezone used for calendar due-date math when none is passed explicitly.
     */
    private function resolveDueDateTimezone(?string $timezone = null): string
    {
        if ($timezone !== null) {
            return TenantTimezone::normalize($timezone);
        }

        return TenantTimezone::normalize(data_get($this->tenant?->settings, 'timezone'));
    }

    /**
     * Get the actual due date based on the due_date setting.
     * Uses the invoice start_date as a calendar Y-m-d in the given timezone (tenant settings when omitted).
     */
    public function getActualDueDate(?string $timezone = null): Carbon
    {
        $tz = $this->resolveDueDateTimezone($timezone);
        $ymd = $this->start_date->format('Y-m-d');
        $start = Carbon::parse($ymd, $tz)->startOfDay();

        return match ($this->due_date) {
            'net_15' => $start->copy()->addDays(15),
            'net_30' => $start->copy()->addDays(30),
            'net_45' => $start->copy()->addDays(45),
            'net_60' => $start->copy()->addDays(60),
            'due_on_receipt' => $start->copy(),
            default => $start->copy()->addDays(30),
        };
    }

    /**
     * API-facing due calendar fields (matches admin UI for net_* / due_on_receipt).
     *
     * @return array{due_calendar_date: ?string, days_until_due: ?int, due_date_timezone: string}
     */
    public function dueDateApiMeta(?string $timezone = null): array
    {
        $tz = $timezone ?? $this->resolveDueDateTimezone(null);

        if ($this->due_date === 'use_payment_terms') {
            return [
                'due_calendar_date' => null,
                'days_until_due' => null,
                'due_date_timezone' => $tz,
            ];
        }

        $due = $this->getActualDueDate($tz);
        $today = now()->timezone($tz)->startOfDay();
        $dueDay = $due->copy()->timezone($tz)->startOfDay();

        return [
            'due_calendar_date' => $dueDay->format('Y-m-d'),
            'days_until_due' => (int) $today->diffInDays($dueDay, false),
            'due_date_timezone' => $tz,
        ];
    }

    /**
     * Check if this invoice has payments in review.
     */
    public function hasPaymentsInReview(): bool
    {
        return $this->payments()->inReview()->exists();
    }

    /**
     * Check if this invoice has rejected payments.
     */
    public function hasRejectedPayments(): bool
    {
        return $this->payments()->rejected()->exists();
    }
}