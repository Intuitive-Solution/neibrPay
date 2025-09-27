<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends ApiController
{
    /**
     * Health check endpoint.
     */
    public function check(): JsonResponse
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            return $this->success([
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'database' => 'connected',
                'version' => app()->version(),
            ], 'Service is healthy');
        } catch (\Exception $e) {
            return $this->error('Service is unhealthy', 503, [
                'database' => 'disconnected',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
