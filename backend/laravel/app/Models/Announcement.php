<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'subject',
        'message',
        'removal_date',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'removal_date' => 'date',
    ];

    /**
     * Get the tenant that owns the announcement.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created this announcement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the recipients for this announcement.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(AnnouncementRecipient::class);
    }

    /**
     * Scope a query to only include announcements for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include active announcements (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('removal_date')
              ->orWhere('removal_date', '>=', now()->toDateString());
        });
    }

    /**
     * Scope a query to only include expired announcements.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('removal_date')
            ->where('removal_date', '<', now()->toDateString());
    }

    /**
     * Check if the announcement is expired.
     */
    public function isExpired(): bool
    {
        return $this->removal_date !== null && $this->removal_date < now()->toDateString();
    }
}
