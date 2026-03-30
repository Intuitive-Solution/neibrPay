<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvoiceReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(
        private readonly InvoiceReminderService $reminderService
    ) {
    }

    public function runInvoiceReminders(Request $request): JsonResponse
    {
        $configuredKey = config('services.n8n.scheduler_api_key');
        $apiKey = $request->header('X-N8N-API-Key');
        if (empty($configuredKey) || empty($apiKey) || !hash_equals((string) $configuredKey, (string) $apiKey)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $result = $this->reminderService->run();

        return response()->json([
            'message' => 'Invoice reminders processed',
            'results' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

