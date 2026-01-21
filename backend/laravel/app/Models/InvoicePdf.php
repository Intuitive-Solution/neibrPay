<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\FileStorageService;

class InvoicePdf extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_unit_id',
        'version',
        'file_name',
        'file_path',
        'file_size',
        'is_latest',
        'generated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'version' => 'integer',
        'file_size' => 'integer',
        'is_latest' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'file_url',
        'file_size_human',
    ];

    /**
     * Get the invoice unit that this PDF belongs to.
     */
    public function invoiceUnit(): BelongsTo
    {
        return $this->belongsTo(InvoiceUnit::class);
    }

    /**
     * Get the user who generated this PDF.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Scope a query to only include the latest version of PDFs.
     */
    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    /**
     * Scope a query to only include PDFs for a specific invoice.
     */
    public function scopeForInvoice($query, $invoiceId)
    {
        return $query->where('invoice_unit_id', $invoiceId);
    }

    /**
     * Scope a query to only include PDFs for a specific version.
     */
    public function scopeForVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Get the file URL for downloading.
     */
    public function getFileUrlAttribute(): string
    {
        return app(FileStorageService::class)->getUrl($this->file_path);
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
     * Check if the file exists in storage.
     */
    public function fileExists(): bool
    {
        return app(FileStorageService::class)->exists($this->file_path);
    }
}
