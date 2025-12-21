<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'budget_category_id',
        'year',
        'month',
        'forecast_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'forecast_amount' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the budget entry.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the budget category for this entry.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    /**
     * Scope a query to only include entries for a specific tenant.
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
     * Scope a query to filter by category.
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('budget_category_id', $categoryId);
    }
}

