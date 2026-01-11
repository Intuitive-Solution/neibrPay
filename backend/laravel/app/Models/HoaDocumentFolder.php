<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HoaDocumentFolder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'parent_id',
        'visible_to_residents',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visible_to_residents' => 'boolean',
    ];

    /**
     * Get the tenant that owns the folder.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created the folder.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(HoaDocumentFolder::class, 'parent_id');
    }

    /**
     * Get the child folders.
     */
    public function children(): HasMany
    {
        return $this->hasMany(HoaDocumentFolder::class, 'parent_id');
    }

    /**
     * Get the documents in this folder.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(HoaDocument::class, 'folder_id');
    }

    /**
     * Scope a query to only include folders for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include folders visible to residents.
     */
    public function scopeVisibleToResidents($query)
    {
        return $query->where('visible_to_residents', true);
    }

    /**
     * Scope a query to only include root folders (no parent).
     */
    public function scopeRootFolders($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if the folder can be deleted.
     * A folder cannot be deleted if it has documents or child folders.
     */
    public function canDelete(): bool
    {
        return $this->documents()->count() === 0 && $this->children()->count() === 0;
    }
}


