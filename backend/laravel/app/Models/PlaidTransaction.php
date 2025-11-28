<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaidTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plaid_bank_account_id',
        'plaid_transaction_id',
        'amount',
        'date',
        'name',
        'merchant_name',
        'category',
        'categories',
        'pending',
        'personal_finance_category',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'pending' => 'boolean',
        'categories' => 'array',
    ];

    /**
     * Get the bank account this transaction belongs to
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(PlaidBankAccount::class, 'plaid_bank_account_id');
    }

    /**
     * Scope: Filter by tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Get posted transactions only
     */
    public function scopePosted($query)
    {
        return $query->where('pending', false);
    }

    /**
     * Scope: Get pending transactions only
     */
    public function scopePending($query)
    {
        return $query->where('pending', true);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by bank account
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('plaid_bank_account_id', $accountId);
    }

    /**
     * Scope: Search by name or merchant
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
                     ->orWhere('merchant_name', 'like', "%{$term}%");
    }
}




