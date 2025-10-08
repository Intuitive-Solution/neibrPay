<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAttachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_unit_id',
        'file_name',
        'file_path',
        'file_hash',
        'file_size',
        'mime_type',
        'attachment_type',
        'uploaded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the invoice unit that this attachment belongs to.
     */
    public function invoiceUnit(): BelongsTo
    {
        return $this->belongsTo(InvoiceUnit::class);
    }

    /**
     * Get the user who uploaded this attachment.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope a query to only include attachments of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('attachment_type', $type);
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if this is a PDF attachment.
     */
    public function isPdf(): bool
    {
        return $this->attachment_type === 'pdf' || $this->mime_type === 'application/pdf';
    }

    /**
     * Check if this is an image attachment.
     */
    public function isImage(): bool
    {
        return $this->attachment_type === 'image' || str_starts_with($this->mime_type, 'image/');
    }
}