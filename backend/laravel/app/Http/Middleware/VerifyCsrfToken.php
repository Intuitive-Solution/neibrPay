<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Public API authentication routes don't need CSRF protection
        'api/auth/check-email',
        'api/auth/send-code',
        'api/auth/verify-code',
        'api/auth/signup',
        'api/auth/login',
        'api/auth/magic-link',
        'api/auth/google/signup',
    ];
}
