<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollRecipient extends Model
{
    use HasFactory;

    public const TYPE_ALL_UNITS = 'all_units';
    public const TYPE_UNIT = 'unit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'poll_id',
        'recipient_type',
        'recipient_id',
    ];

    /**
     * Get the poll that owns this recipient.
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
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
