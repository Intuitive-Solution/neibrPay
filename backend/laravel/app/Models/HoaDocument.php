<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\FileStorageService;

class HoaDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'folder_id',
        'file_name',
        'file_path',
        'file_hash',
        'file_size',
        'mime_type',
        'description',
        'visible_to_residents',
        'uploaded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'visible_to_residents' => 'boolean',
    ];

    /**
     * Get the tenant that owns the document.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the folder that contains this document.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(HoaDocumentFolder::class, 'folder_id');
    }

    /**
     * Scope a query to only include documents for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include documents visible to residents.
     */
    public function scopeVisibleToResidents($query)
    {
        return $query->where('visible_to_residents', true);
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
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Check if the file exists in storage.
     */
    public function fileExists(): bool
    {
        return app(FileStorageService::class)->exists($this->file_path);
    }
}
