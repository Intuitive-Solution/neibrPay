<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlaidBankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'plaid_item_id',
        'plaid_access_token',
        'institution_id',
        'institution_name',
        'account_id',
        'account_name',
        'account_mask',
        'current_balance',
        'available_balance',
        'sync_start_date',
        'last_synced_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'sync_start_date' => 'date',
        'last_synced_at' => 'datetime',
        'current_balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
    ];

    /**
     * Get transactions for this bank account
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PlaidTransaction::class);
    }

    /**
     * Scope: Filter by tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Get active accounts only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Accessor: Get decrypted access token
     */
    public function getDecryptedAccessToken(): string
    {
        return decrypt($this->plaid_access_token);
    }

    /**
     * Mutator: Encrypt access token before saving
     */
    public function setPlaidAccessTokenAttribute($value): void
    {
        $this->attributes['plaid_access_token'] = encrypt($value);
    }
}




