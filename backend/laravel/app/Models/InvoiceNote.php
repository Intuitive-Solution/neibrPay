<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceNote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_unit_id',
        'type',
        'content',
    ];

    /**
     * Get the invoice unit that owns this note.
     */
    public function invoiceUnit(): BelongsTo
    {
        return $this->belongsTo(InvoiceUnit::class);
    }

    /**
     * Scope a query to only include notes of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}