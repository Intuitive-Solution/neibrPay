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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
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
        });
    }
}