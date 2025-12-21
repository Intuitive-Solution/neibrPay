<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetAuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'year',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the tenant that owns the audit log.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include logs for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope a query to order by most recent first.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

