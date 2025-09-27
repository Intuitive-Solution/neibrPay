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
            $data = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'version' => app()->version(),
                'environment' => app()->environment(),
            ];

            // Try to check database connection, but don't fail if it's not configured
            try {
                DB::connection()->getPdo();
                $data['database'] = 'connected';
            } catch (\Exception $e) {
                $data['database'] = 'not_configured';
                $data['database_note'] = 'Database connection not configured yet';
            }
            
            return $this->success($data, 'Service is healthy');
        } catch (\Exception $e) {
            return $this->error('Service is unhealthy', 503, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
