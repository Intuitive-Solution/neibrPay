<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileStorageService
{
    /**
     * Build candidate S3 keys for reading when legacy absolute paths exist.
     *
     * @return string[]
     */
    private function candidatePaths(string $path): array
    {
        $candidates = [];
        $candidates[] = $path;

        if (str_starts_with($path, '/')) {
            $candidates[] = ltrim($path, '/');
        }

        $storagePrefix = rtrim(storage_path('app/public'), '/');

        if (str_starts_with($path, $storagePrefix)) {
            $relative = ltrim(substr($path, strlen($storagePrefix)), '/');
            if ($relative !== '') {
                $candidates[] = $relative;
            }
        } else {
            $candidates[] = $storagePrefix . '/' . ltrim($path, '/');
            $candidates[] = ltrim($storagePrefix . '/' . ltrim($path, '/'), '/');
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * Resolve the correct path for read operations (S3 legacy absolute keys).
     */
    private function resolveReadablePath(string $path): string
    {
        if (!$this->isS3()) {
            return $path;
        }

        foreach ($this->candidatePaths($path) as $candidate) {
            try {
                if ($this->disk()->exists($candidate)) {
                    return $candidate;
                }
            } catch (\Exception $e) {
                Log::warning('FileStorageService::resolveReadablePath exists failed', [
                    'path' => $path,
                    'candidate' => $candidate,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $path;
    }

    /**
     * Get the configured disk name.
     */
    protected function getDiskName(): string
    {
        return 'documents';
    }

    /**
     * Get the storage disk instance.
     */
    protected function disk()
    {
        return Storage::disk($this->getDiskName());
    }

    /**
     * Check if using S3 storage.
     */
    protected function isS3(): bool
    {
        return env('FILESYSTEM_DISK', 'public') === 's3';
    }

    /**
     * Public check for S3 storage (for controllers).
     */
    public function isS3Disk(): bool
    {
        return $this->isS3();
    }

    /**
     * Store file contents at the given path.
     */
    public function store(string $path, string $contents): bool
    {
        try {
            return $this->disk()->put($path, $contents) !== false;
        } catch (\Exception $e) {
            Log::error('FileStorageService::store failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Store a file at the given path with a specific name.
     */
    public function storeAs(string $path, $file, string $name): string
    {
        try {
            $fullPath = rtrim($path, '/') . '/' . $name;
            $this->disk()->put($fullPath, file_get_contents($file));
            return $fullPath;
        } catch (\Exception $e) {
            Log::error('FileStorageService::storeAs failed', [
                'path' => $path,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a file at the given path.
     */
    public function delete(string $path): bool
    {
        try {
            $resolvedPath = $this->resolveReadablePath($path);
            if ($this->disk()->exists($resolvedPath)) {
                return $this->disk()->delete($resolvedPath);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('FileStorageService::delete failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a file exists at the given path.
     */
    public function exists(string $path): bool
    {
        try {
            $resolvedPath = $this->resolveReadablePath($path);
            return $this->disk()->exists($resolvedPath);
        } catch (\Exception $e) {
            Log::error('FileStorageService::exists failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get file contents.
     */
    public function get(string $path): ?string
    {
        try {
            $resolvedPath = $this->resolveReadablePath($path);
            if (!$this->disk()->exists($resolvedPath)) {
                return null;
            }
            return $this->disk()->get($resolvedPath);
        } catch (\Exception $e) {
            Log::error('FileStorageService::get failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get a URL for the file (signed URL for S3, regular URL for local).
     */
    public function getUrl(string $path): string
    {
        try {
            $resolvedPath = $this->resolveReadablePath($path);
            if ($this->isS3()) {
                // Generate signed URL with 60 minute expiration for S3
                /** @var \Illuminate\Contracts\Filesystem\Filesystem $disk */
                $disk = $this->disk();
                try {
                    if (method_exists($disk, 'temporaryUrl')) {
                        return $disk->temporaryUrl($resolvedPath, now()->addMinutes(60));
                    }
                } catch (\Exception $signedUrlError) {
                    Log::warning('Failed to generate S3 signed URL, falling back to url()', [
                        'path' => $resolvedPath,
                        'error' => $signedUrlError->getMessage(),
                    ]);
                    // Fallback to regular URL if signed URL fails
                    if (method_exists($disk, 'url')) {
                        return $disk->url($resolvedPath);
                    }
                }
            } else {
                // For local storage, return public URL
                /** @var \Illuminate\Contracts\Filesystem\Filesystem $disk */
                $disk = $this->disk();
                if (method_exists($disk, 'url')) {
                    return $disk->url($resolvedPath);
                }
            }
        } catch (\Exception $e) {
            Log::error('FileStorageService::getUrl failed', [
                'path' => $path,
                'error' => $e->getMessage(),
                'is_s3' => $this->isS3(),
            ]);
        }
        // Fallback to asset URL for local storage
        return asset('storage/' . $path);
    }

    /**
     * Get a short-lived signed URL for the file (S3) or public URL (local).
     */
    public function getTemporaryUrl(string $path, int $minutes = 6, array $options = []): string
    {
        try {
            /** @var \Illuminate\Contracts\Filesystem\Filesystem $disk */
            $disk = $this->disk();
            $resolvedPath = $this->resolveReadablePath($path);

            if ($this->isS3() && method_exists($disk, 'temporaryUrl')) {
                return $disk->temporaryUrl(
                    $resolvedPath,
                    now()->addMinutes($minutes),
                    $options
                );
            }

            if (method_exists($disk, 'url')) {
                return $disk->url($resolvedPath);
            }
        } catch (\Exception $e) {
            Log::error('FileStorageService::getTemporaryUrl failed', [
                'path' => $path,
                'minutes' => $minutes,
                'error' => $e->getMessage(),
                'is_s3' => $this->isS3(),
            ]);
        }

        return asset('storage/' . $path);
    }

    /**
     * Get a download response for the file.
     */
    public function getDownloadResponse(string $path, string $filename): \Symfony\Component\HttpFoundation\Response
    {
        try {
            if ($this->isS3()) {
                // For S3, stream the file
                $fileContents = $this->get($path);
                if ($fileContents === null) {
                    abort(404, 'File not found');
                }
                
                // Try to get MIME type, fallback to octet-stream
                $mimeType = 'application/octet-stream';
                /** @var \Illuminate\Contracts\Filesystem\Filesystem $disk */
                $disk = $this->disk();
                if (method_exists($disk, 'mimeType')) {
                    $mimeType = $disk->mimeType($path) ?? $mimeType;
                }
                
                return response($fileContents, 200, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            } else {
                // For local storage, use response()->download()
                $resolvedPath = $this->resolveReadablePath($path);
                $fullPath = str_starts_with($resolvedPath, '/')
                    ? $resolvedPath
                    : storage_path('app/documents/' . ltrim($resolvedPath, '/'));
                if (!file_exists($fullPath)) {
                    abort(404, 'File not found');
                }
                return response()->download($fullPath, $filename);
            }
        } catch (\Exception $e) {
            Log::error('FileStorageService::getDownloadResponse failed', [
                'path' => $path,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Failed to download file');
        }
    }

    /**
     * Make a directory (for local storage only).
     */
    public function makeDirectory(string $path): bool
    {
        try {
            if (!$this->isS3()) {
                // S3 doesn't need directories, but local storage does
                if (!$this->disk()->exists($path)) {
                    return $this->disk()->makeDirectory($path);
                }
                return true;
            }
            return true; // S3 doesn't need directories
        } catch (\Exception $e) {
            Log::error('FileStorageService::makeDirectory failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Move a file from one path to another.
     */
    public function move(string $from, string $to): bool
    {
        try {
            if ($this->isS3()) {
                // For S3, copy then delete
                if ($this->disk()->copy($from, $to)) {
                    return $this->disk()->delete($from);
                }
                return false;
            } else {
                // For local storage, use move
                return $this->disk()->move($from, $to);
            }
        } catch (\Exception $e) {
            Log::error('FileStorageService::move failed', [
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
