<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UnitDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'tenant_id',
        'file_name',
        'file_path',
        'file_hash',
        'file_size',
        'mime_type',
        'description',
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
     * Get the unit that owns the document.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

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
     * Scope a query to only include documents for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include documents for a specific unit.
     */
    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
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
     * Delete the file from storage when model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if ($document->isForceDeleting()) {
                // Only delete file from storage on force delete
                if ($document->fileExists()) {
                    Storage::disk('public')->delete($document->file_path);
                }
            }
        });
    }
}
