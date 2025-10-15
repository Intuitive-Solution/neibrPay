<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'vendor_id',
        'invoice_number',
        'invoice_date',
        'invoice_due_date',
        'invoice_amount',
        'category',
        'note',
        'status',
        'payment_details',
        'payment_method',
        'paid_amount',
        'paid_date',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'invoice_due_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_date' => 'date',
        'category' => 'string',
        'status' => 'string',
        'payment_method' => 'string',
    ];

    /**
     * Get the tenant that owns the expense.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the vendor for this expense.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user who created this expense.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attachments for this expense.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ExpenseAttachment::class);
    }

    /**
     * Scope a query to only include expenses for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to filter by vendor.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to search expenses by invoice number or note.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhere('note', 'like', "%{$search}%");
        });
    }

    /**
     * Get the remaining balance for this expense.
     */
    public function getBalanceAttribute(): float
    {
        return $this->invoice_amount - $this->paid_amount;
    }

    /**
     * Check if the expense is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->invoice_due_date < now()->toDateString();
    }
}