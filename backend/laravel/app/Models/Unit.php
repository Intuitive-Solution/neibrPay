<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'title',
        'address',
        'city',
        'state',
        'zip_code',
        'starting_balance',
        'balance_as_of_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starting_balance' => 'decimal:2',
        'balance_as_of_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the unit.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the users (owners) that own this unit.
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'unit_owners', 'unit_id', 'resident_id')
            ->withTimestamps();
    }

    /**
     * Get the documents associated with this unit.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(UnitDocument::class);
    }

    /**
     * Get the invoices associated with this unit.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceUnit::class);
    }

    /**
     * Scope a query to only include units for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include active units.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include inactive units.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}