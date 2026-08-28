<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Poll extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public const VISIBILITY_AFTER_CLOSE = 'after_close';
    public const VISIBILITY_LIVE = 'live';
    public const VISIBILITY_ADMINS_ONLY = 'admins_only';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'status',
        'opens_at',
        'closes_at',
        'results_visibility',
        'anonymous_responses',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'anonymous_responses' => 'boolean',
    ];

    /**
     * Get the tenant that owns the poll.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created this poll.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the questions for this poll.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(PollQuestion::class)->orderBy('sort_order');
    }

    /**
     * Get the recipients (targeting rules) for this poll.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(PollRecipient::class);
    }

    /**
     * Get the participation records for this poll.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class);
    }

    /**
     * Scope a query to only include polls for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to polls residents can see (published, i.e. not draft).
     */
    public function scopePublished($query)
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_CLOSED]);
    }

    /**
     * Scope a query to open polls.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Check whether the poll is currently accepting votes.
     */
    public function isAcceptingVotes(): bool
    {
        if ($this->status !== self::STATUS_OPEN) {
            return false;
        }

        if ($this->opens_at && $this->opens_at->isFuture()) {
            return false;
        }

        if ($this->closes_at && $this->closes_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Whether a non-admin caller is allowed to see the tally.
     */
    public function resultsVisibleToResidents(): bool
    {
        return match ($this->results_visibility) {
            self::VISIBILITY_LIVE => true,
            self::VISIBILITY_AFTER_CLOSE => $this->status === self::STATUS_CLOSED,
            default => false,
        };
    }

    /**
     * Unit ids this poll targets, resolved from the recipient rules.
     */
    public function targetUnitIds(): Collection
    {
        $recipients = $this->relationLoaded('recipients')
            ? $this->recipients
            : $this->recipients()->get();

        $targetsAll = $recipients->contains(
            fn ($recipient) => $recipient->recipient_type === PollRecipient::TYPE_ALL_UNITS
        );

        if ($targetsAll) {
            return Unit::forTenant($this->tenant_id)
                ->where('is_active', true)
                ->pluck('id');
        }

        return $recipients
            ->where('recipient_type', PollRecipient::TYPE_UNIT)
            ->pluck('recipient_id')
            ->filter()
            ->unique()
            ->values();
    }
}
