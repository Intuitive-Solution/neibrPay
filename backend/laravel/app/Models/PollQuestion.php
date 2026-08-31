<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollQuestion extends Model
{
    use HasFactory;

    public const TYPE_SINGLE_CHOICE = 'single_choice';
    public const TYPE_MULTI_SELECT = 'multi_select';
    public const TYPE_YES_NO = 'yes_no';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'poll_id',
        'prompt',
        'type',
        'sort_order',
    ];

    /**
     * Get the poll that owns this question.
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Get the options for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    /**
     * Whether this question accepts more than one selected option.
     */
    public function allowsMultipleAnswers(): bool
    {
        return $this->type === self::TYPE_MULTI_SELECT;
    }
}
