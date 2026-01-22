<?php

namespace App\Http\Controllers\Api;

use App\Services\DocumentBackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController
{
    private DocumentBackupService $backupService;

    public function __construct(DocumentBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * POST /api/backup/documents
     * 
     * Endpoint to trigger document backup. Secured with X-N8N-API-Key header.
     * Called by n8n workflow on a daily schedule.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function backupDocuments(Request $request): JsonResponse
    {
        try {
            // Verify API key (same pattern as plaid/sync-all)
            $apiKey = $request->header('X-N8N-API-Key');
            if ($apiKey !== config('backup.api_key')) {
                Log::warning('Backup endpoint called with invalid API key');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Execute backup
            $result = $this->backupService->backup();

            return response()->json([
                'success' => true,
                'message' => 'Backup completed successfully',
                'data' => $result,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Document backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
