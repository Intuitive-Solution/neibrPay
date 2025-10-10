<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        return asset('storage/' . $this->file_path);
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
        return Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Get the full file path in storage.
     */
    public function getFullFilePath(): string
    {
        return storage_path('app/public/' . $this->file_path);
    }

    /**
     * Get the next version number for an invoice.
     */
    public static function getNextVersion(int $invoiceUnitId): int
    {
        $latestVersion = self::where('invoice_unit_id', $invoiceUnitId)
            ->max('version');
        
        return $latestVersion ? $latestVersion + 1 : 1;
    }

    /**
     * Mark all other versions of this invoice as not latest.
     */
    public function markOthersAsNotLatest(): void
    {
        self::where('invoice_unit_id', $this->invoice_unit_id)
            ->where('id', '!=', $this->id)
            ->update(['is_latest' => false]);
    }
}
