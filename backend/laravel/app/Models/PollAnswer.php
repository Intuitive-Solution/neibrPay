<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'poll_response_id',
        'poll_question_id',
        'poll_option_id',
    ];

    /**
     * Get the participation record this answer belongs to.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(PollResponse::class, 'poll_response_id');
    }

    /**
     * Get the question this answer belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(PollQuestion::class, 'poll_question_id');
    }

    /**
     * Get the option that was selected.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }
}
