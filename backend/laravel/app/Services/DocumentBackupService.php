<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class DocumentBackupService
{
    /**
     * Execute document backup: create ZIP and upload to S3
     *
     * @return array Backup result with metadata
     * @throws \Exception
     */
    public function backup(): array
    {
        $sourcePath = config('backup.documents.source_path');
        
        // Verify source directory exists
        if (!is_dir($sourcePath)) {
            throw new \Exception("Documents directory not found: {$sourcePath}");
        }

        // Create temporary ZIP file
        $tempZipPath = $this->createZipArchive($sourcePath);
        
        try {
            // Verify ZIP was created
            if (!file_exists($tempZipPath)) {
                throw new \Exception("Failed to create ZIP archive");
            }

            // Get ZIP file info
            $zipSize = filesize($tempZipPath);
            $zipFilename = basename($tempZipPath);

            // Upload to S3
            $s3Path = $this->uploadToS3($tempZipPath, $zipFilename);

            // Count files in backup
            $fileCount = $this->countBackupFiles($sourcePath);

            // Prepare response
            $result = [
                'filename' => $zipFilename,
                'file_count' => $fileCount,
                'total_size_bytes' => $zipSize,
                'total_size_human' => $this->formatBytes($zipSize),
                's3_path' => $s3Path,
                's3_bucket' => config('backup.documents.s3_bucket'),
            ];

            Log::info('Document backup completed successfully', $result);

            return $result;
        } finally {
            // Always clean up temp file
            $this->cleanupTempFile($tempZipPath);
        }
    }

    /**
     * Create a ZIP archive of all documents
     *
     * @param string $sourcePath
     * @return string Path to created ZIP file
     * @throws \Exception
     */
    private function createZipArchive(string $sourcePath): string
    {
        $timestamp = now()->format('Y-m-d');
        $environment = config('app.env', 'production');
        $zipFilename = "neibrpay-documents-{$environment}-{$timestamp}.zip";
        $tempZipPath = sys_get_temp_dir() . '/' . $zipFilename;

        $zip = new ZipArchive();
        $openResult = $zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            throw new \Exception("Failed to open ZIP archive: error code {$openResult}");
        }

        try {
            $this->addDirectoryToZip($zip, $sourcePath, '');
            
            if (!$zip->close()) {
                throw new \Exception("Failed to close ZIP archive properly");
            }

            Log::info("ZIP archive created", ['path' => $tempZipPath, 'size' => filesize($tempZipPath)]);

            return $tempZipPath;
        } catch (\Exception $e) {
            $zip->close();
            if (file_exists($tempZipPath)) {
                unlink($tempZipPath);
            }
            throw $e;
        }
    }

    /**
     * Recursively add directory contents to ZIP
     *
     * @param ZipArchive $zip
     * @param string $dir Current directory path
     * @param string $zipPath Path within ZIP
     * @return void
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = scandir($dir);
        
        if ($files === false) {
            Log::warning("Unable to read directory", ['dir' => $dir]);
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . '/' . $file;
            $zipFilePath = rtrim($zipPath, '/') . '/' . $file;

            if (is_dir($filePath)) {
                $this->addDirectoryToZip($zip, $filePath, $zipFilePath);
            } else {
                $contents = file_get_contents($filePath);
                if ($contents !== false) {
                    $zip->addFromString($zipFilePath, $contents);
                } else {
                    Log::warning("Failed to read file", ['file' => $filePath]);
                }
            }
        }
    }

    /**
     * Upload ZIP file to S3
     *
     * @param string $localPath Path to local ZIP file
     * @param string $filename ZIP filename
     * @return string S3 path
     * @throws \Exception
     */
    private function uploadToS3(string $localPath, string $filename): string
    {
        $s3Prefix = config('backup.documents.s3_prefix');
        $s3Path = rtrim($s3Prefix, '/') . '/' . $filename;

        $fileContents = file_get_contents($localPath);
        if ($fileContents === false) {
            throw new \Exception("Failed to read ZIP file for upload: {$localPath}");
        }

        $disk = Storage::disk('s3');
        $uploadSuccess = $disk->put($s3Path, $fileContents);

        if (!$uploadSuccess) {
            throw new \Exception("Failed to upload to S3 at path: {$s3Path}");
        }

        Log::info("File uploaded to S3", [
            's3_path' => $s3Path,
            'size' => strlen($fileContents),
        ]);

        return $s3Path;
    }

    /**
     * Count total files in documents directory
     *
     * @param string $sourcePath
     * @return int
     */
    private function countBackupFiles(string $sourcePath): int
    {
        $count = 0;
        $this->countFilesRecursive($sourcePath, $count);
        return $count;
    }

    /**
     * Recursively count files
     *
     * @param string $dir
     * @param int &$count
     * @return void
     */
    private function countFilesRecursive(string $dir, int &$count): void
    {
        $files = scandir($dir);
        
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . '/' . $file;

            if (is_dir($filePath)) {
                $this->countFilesRecursive($filePath, $count);
            } else {
                $count++;
            }
        }
    }

    /**
     * Format bytes to human-readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Clean up temporary ZIP file
     *
     * @param string $filePath
     * @return void
     */
    private function cleanupTempFile(string $filePath): void
    {
        try {
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::debug("Temporary file cleaned up", ['path' => $filePath]);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to cleanup temporary file", [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
