<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_unit_id',
        'amount',
        'payment_method',
        'payment_reference',
        'notes',
        'payment_date',
        'recorded_by',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_payment_method',
        'status',
        'admin_comment_public',
        'admin_comment_private',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the invoice unit that this payment belongs to.
     */
    public function invoiceUnit(): BelongsTo
    {
        return $this->belongsTo(InvoiceUnit::class);
    }

    /**
     * Get the user who recorded this payment.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the user who reviewed this payment.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope a query to only include payments for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include payments by a specific method.
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to only include confirmed payments (exclude temporary Stripe payments).
     * Temporary Stripe payments are those with payment_method='stripe_card' or 'stripe_ach' 
     * and stripe_payment_intent_id=null.
     */
    public function scopeConfirmed($query)
    {
        return $query->where(function ($q) {
            $q->whereNotIn('payment_method', ['stripe_card', 'stripe_ach'])
                ->orWhereNotNull('stripe_payment_intent_id');
        })->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in-review payments.
     */
    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    /**
     * Scope a query to only include approved payments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected payments.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if payment can be reviewed.
     */
    public function canBeReviewed(): bool
    {
        return $this->status === 'in_review';
    }

    /**
     * Check if payment can be resubmitted.
     */
    public function canBeResubmitted(): bool
    {
        return $this->status === 'rejected';
    }
}