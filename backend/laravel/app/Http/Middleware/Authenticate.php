<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API routes, always return JSON 401 response
        if ($request->is('api/*')) {
            return null;
        }
        
        // For web routes expecting JSON, return null (401 response)
        if ($request->expectsJson()) {
            return null;
        }
        
        // Try to redirect to login route, but return null if route doesn't exist
        try {
            return route('login');
        } catch (\Exception $e) {
            // If login route doesn't exist, return null to get 401 JSON response
            return null;
        }
    }
}
