<?php

namespace App\Models;

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
        'discount_amount',
        'discount_type',
        'auto_bill',
        'items',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'paid_to_date',
        'balance_due',
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
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_to_date' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'discount_amount' => 'decimal:2',
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
        $this->balance_due = (float) ($this->total - $this->paid_to_date);
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

        $dueDate = $this->start_date;
        
        // Calculate actual due date based on due_date setting
        switch ($this->due_date) {
            case 'net_15':
                $dueDate = $this->start_date->copy()->addDays(15);
                break;
            case 'net_30':
                $dueDate = $this->start_date->copy()->addDays(30);
                break;
            case 'net_45':
                $dueDate = $this->start_date->copy()->addDays(45);
                break;
            case 'net_60':
                $dueDate = $this->start_date->copy()->addDays(60);
                break;
            case 'due_on_receipt':
                $dueDate = $this->start_date;
                break;
        }

        return $dueDate->isPast() && $this->balance_due > 0;
    }

    /**
     * Get the actual due date based on the due_date setting.
     */
    public function getActualDueDate()
    {
        $dueDate = $this->start_date;
        
        switch ($this->due_date) {
            case 'net_15':
                return $dueDate->copy()->addDays(15);
            case 'net_30':
                return $dueDate->copy()->addDays(30);
            case 'net_45':
                return $dueDate->copy()->addDays(45);
            case 'net_60':
                return $dueDate->copy()->addDays(60);
            case 'due_on_receipt':
                return $dueDate;
            default:
                return $dueDate->copy()->addDays(30); // Default to net 30
        }
    }
}