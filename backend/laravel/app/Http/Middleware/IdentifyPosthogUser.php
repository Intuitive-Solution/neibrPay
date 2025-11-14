<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyPosthogUser
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Identify user in PostHog
            $this->analytics->identifyUser($user->id, [
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
                'tenant_name' => $user->tenant?->name,
                'is_admin' => $user->role === 'admin',
                'is_resident' => $user->role === 'resident',
                'is_bookkeeper' => $user->role === 'bookkeeper',
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ]);
        }

        return $next($request);
    }
}

