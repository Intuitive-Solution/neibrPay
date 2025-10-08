<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_unit_id',
        'next_due_date',
        'remaining_cycles',
        'is_active',
        'last_generated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'next_due_date' => 'date',
        'is_active' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Get the invoice unit that this schedule belongs to.
     */
    public function invoiceUnit(): BelongsTo
    {
        return $this->belongsTo(InvoiceUnit::class);
    }

    /**
     * Scope a query to only include active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include schedules due on or before a specific date.
     */
    public function scopeDueOnOrBefore($query, $date)
    {
        return $query->where('next_due_date', '<=', $date);
    }

    /**
     * Check if this schedule has remaining cycles.
     */
    public function hasRemainingCycles(): bool
    {
        return $this->remaining_cycles === null || $this->remaining_cycles > 0;
    }

    /**
     * Decrement the remaining cycles.
     */
    public function decrementCycles(): void
    {
        if ($this->remaining_cycles !== null && $this->remaining_cycles > 0) {
            $this->remaining_cycles--;
            
            if ($this->remaining_cycles <= 0) {
                $this->is_active = false;
            }
        }
    }

    /**
     * Calculate the next due date based on frequency.
     */
    public function calculateNextDueDate(): void
    {
        $invoice = $this->invoiceUnit;
        $currentDate = $this->next_due_date ?? $invoice->start_date;

        switch ($invoice->frequency) {
            case 'weekly':
                $this->next_due_date = $currentDate->addWeek();
                break;
            case 'monthly':
                $this->next_due_date = $currentDate->addMonth();
                break;
            case 'quarterly':
                $this->next_due_date = $currentDate->addMonths(3);
                break;
            case 'yearly':
                $this->next_due_date = $currentDate->addYear();
                break;
            default:
                $this->next_due_date = $currentDate->addMonth();
        }
    }
}