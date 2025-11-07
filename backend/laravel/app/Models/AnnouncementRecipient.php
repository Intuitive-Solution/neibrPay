<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRecipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'announcement_id',
        'recipient_type',
        'recipient_id',
    ];

    /**
     * Get the announcement that owns this recipient.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the resident (user) recipient.
     * Only applicable when recipient_type is 'resident'.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the unit recipient.
     * Only applicable when recipient_type is 'unit'.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'recipient_id');
    }
}
