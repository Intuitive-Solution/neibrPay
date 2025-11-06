<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\VerificationCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    protected VerificationCodeService $verificationCodeService;

    public function __construct(VerificationCodeService $verificationCodeService)
    {
        $this->verificationCodeService = $verificationCodeService;
    }

    /**
     * Check if email exists in the system
     */
    public function checkEmail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $email = strtolower(trim($validated['email']));
            $user = User::where('email', $email)->first();

            if ($user) {
                return response()->json([
                    'exists' => true,
                    'requires_signup' => false,
                    'tenant_name' => $user->tenant?->name,
                ]);
            }

            return response()->json([
                'exists' => false,
                'requires_signup' => true,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Check email failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check email'], 500);
        }
    }

    /**
     * Send verification code to email
     */
    public function sendCode(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $email = strtolower(trim($validated['email']));
            $ipAddress = $request->ip();

            // Check if user exists to determine rate limit
            $user = User::where('email', $email)->with('tenant')->first();
            $isExistingUser = $user !== null;
            $userName = $user?->name;
            $tenantName = $user?->tenant?->name;

            // Check rate limits (10 for existing users, 3 for new users)
            if (!$this->verificationCodeService->canSendCode($email, $isExistingUser)) {
                $maxCodes = $isExistingUser ? 10 : 3;
                return response()->json([
                    'error' => "Too many verification code requests. Maximum {$maxCodes} codes per hour allowed. Please try again later.",
                ], 429);
            }

            // Generate and store code
            $verificationCode = $this->verificationCodeService->generateCode($email, null, $ipAddress, $isExistingUser);

            // Send email via n8n
            $this->verificationCodeService->sendCodeEmail($email, $verificationCode->code, $userName, $tenantName);

            return response()->json([
                'message' => 'Verification code sent',
                'expires_in' => $this->verificationCodeService->getExpirationSeconds(),
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Send code failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify code and return temporary verification token
     */
    public function verifyCode(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'code' => 'required|string|size:6',
            ]);

            $email = strtolower(trim($validated['email']));
            $code = $validated['code'];

            // Validate code
            $verificationCode = $this->verificationCodeService->validateCode($email, $code);

            if (!$verificationCode) {
                return response()->json([
                    'valid' => false,
                    'error' => 'Invalid or expired verification code',
                ], 400);
            }

            // Generate temporary verification token (stored in cache for 10 minutes)
            $verificationToken = Str::random(64);
            Cache::put("verification_token:{$verificationToken}", [
                'email' => $email,
                'code_id' => $verificationCode->id,
                'verified_at' => now(),
            ], now()->addMinutes(10));

            return response()->json([
                'valid' => true,
                'verification_token' => $verificationToken,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Verify code failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to verify code'], 500);
        }
    }

    /**
     * Signup new user with verification code
     */
    public function signup(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'verification_token' => 'required|string',
                'full_name' => 'required|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'community_name' => 'required|string|max:255',
            ]);

            // Validate verification token
            $tokenData = Cache::get("verification_token:{$validated['verification_token']}");
            if (!$tokenData || $tokenData['email'] !== strtolower(trim($validated['email']))) {
                return response()->json(['error' => 'Invalid verification token'], 400);
            }

            $email = strtolower(trim($validated['email']));

            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                return response()->json(['error' => 'User already exists'], 400);
            }

            // Check if community already exists
            $existingTenant = Tenant::where('name', $validated['community_name'])->first();
            if ($existingTenant) {
                return response()->json(['error' => 'Community already exists'], 400);
            }

            DB::beginTransaction();

            try {
                // Create tenant
                $tenant = Tenant::create([
                    'name' => $validated['community_name'],
                    'slug' => Str::slug($validated['community_name']),
                    'trial_ends_at' => now()->addDays(30),
                    'is_active' => true,
                ]);

                // Create user
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $validated['full_name'],
                    'email' => $email,
                    'phone_number' => $validated['phone_number'] ?? null,
                    'role' => 'admin',
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

                // Generate Sanctum token
                $token = $user->createToken('auth-token')->plainTextToken;

                // Delete verification token from cache
                Cache::forget("verification_token:{$validated['verification_token']}");

                DB::commit();

                return response()->json([
                    'message' => 'Account created successfully',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone_number' => $user->phone_number,
                        'email_verified' => true,
                    ],
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'trial_ends_at' => $tenant->trial_ends_at,
                    ],
                    'token' => $token,
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Signup failed: ' . $e->getMessage());
            return response()->json(['error' => 'Signup failed'], 500);
        }
    }

    /**
     * Login existing user with verification code
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'verification_token' => 'required|string',
            ]);

            // Validate verification token
            $tokenData = Cache::get("verification_token:{$validated['verification_token']}");
            if (!$tokenData || $tokenData['email'] !== strtolower(trim($validated['email']))) {
                return response()->json(['error' => 'Invalid verification token'], 400);
            }

            $email = strtolower(trim($validated['email']));

            // Find user
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json(['error' => 'Account is inactive'], 403);
            }

            // Update last login
            $user->updateLastLogin();

            // Generate Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Delete verification token from cache
            Cache::forget("verification_token:{$validated['verification_token']}");

            $user->load('tenant');

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone_number' => $user->phone_number,
                    'email_verified' => $user->email_verified_at !== null,
                ],
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'slug' => $user->tenant->slug,
                    'trial_ends_at' => $user->tenant->trial_ends_at,
                ] : null,
                'token' => $token,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return response()->json(['error' => 'Login failed'], 500);
        }
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        try {
            // Check if Google OAuth is configured
            $clientId = config('services.google.client_id');
            $clientSecret = config('services.google.client_secret');
            $redirectUri = config('services.google.redirect');
            
            Log::info('Google OAuth redirect attempt', [
                'client_id' => $clientId ? 'set' : 'missing',
                'client_secret' => $clientSecret ? 'set' : 'missing',
                'redirect_uri' => $redirectUri,
                'env_redirect' => env('GOOGLE_REDIRECT_URI'),
                'app_url' => config('app.url'),
            ]);
            
            if (empty($clientId) || empty($clientSecret)) {
                Log::error('Google OAuth not configured: Missing GOOGLE_CLIENT_ID or GOOGLE_CLIENT_SECRET');
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
                return redirect("{$frontendUrl}/auth?error=" . urlencode('Google OAuth is not configured. Please contact support.'));
            }
            
            // Explicitly set the redirect URI to ensure it matches Google Cloud Console
            // Remove any trailing slashes and ensure exact match
            $redirectUri = rtrim($redirectUri, '/');
            
            Log::info('Google OAuth redirect URI being used', [
                'redirect_uri' => $redirectUri,
                'config_redirect' => config('services.google.redirect'),
                'env_redirect' => env('GOOGLE_REDIRECT_URI'),
            ]);
            
            // Explicitly set redirect URL to ensure exact match with Google Cloud Console
            return Socialite::driver('google')
                ->redirectUrl($redirectUri)
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Google redirect failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect("{$frontendUrl}/auth?error=" . urlencode('Failed to initiate Google authentication: ' . $e->getMessage()));
        }
    }

    /**
     * Handle Google OAuth callback
     * Redirects to frontend with result data in query params
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            Log::info('Google OAuth callback received', [
                'query_params' => $request->query(),
                'has_code' => $request->has('code'),
                'has_state' => $request->has('state'),
            ]);

            $googleUser = Socialite::driver('google')->user();

            $email = strtolower(trim($googleUser->getEmail()));
            $name = $googleUser->getName();
            $avatar = $googleUser->getAvatar();

            Log::info('Google user data retrieved', [
                'email' => $email,
                'name' => $name,
                'has_avatar' => !empty($avatar),
            ]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            // Check if user exists
            $user = User::where('email', $email)->first();

            if ($user) {
                // Existing user - login
                if (!$user->is_active) {
                    Log::warning('Google OAuth login attempt for inactive user', ['email' => $email]);
                    return redirect("{$frontendUrl}/auth?error=" . urlencode('Account is inactive'));
                }

                // Update user info from Google
                $user->update([
                    'name' => $name ?? $user->name,
                    'avatar_url' => $avatar ?? $user->avatar_url,
                    'email_verified_at' => now(),
                ]);

                $user->updateLastLogin();

                // Generate Sanctum token
                $token = $user->createToken('auth-token')->plainTextToken;

                $user->load('tenant');

                Log::info('Google OAuth login successful', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);

                // Redirect to frontend with token and user data
                $params = http_build_query([
                    'token' => $token,
                    'user_id' => $user->id,
                    'exists' => 'true',
                ]);

                return redirect("{$frontendUrl}/auth?{$params}");
            } else {
                // New user - return Google data for signup
                $googleToken = Str::random(64);
                Cache::put("google_signup:{$googleToken}", [
                    'email' => $email,
                    'name' => $name,
                    'avatar' => $avatar,
                    'verified_at' => now(),
                ], now()->addMinutes(30));

                Log::info('Google OAuth new user signup', [
                    'email' => $email,
                    'google_token' => $googleToken,
                ]);

                // Redirect to frontend with Google data
                $params = http_build_query([
                    'google_token' => $googleToken,
                    'email' => $email,
                    'name' => $name,
                    'avatar' => $avatar ?? '',
                    'exists' => 'false',
                ]);

                return redirect("{$frontendUrl}/auth?{$params}");
            }

        } catch (\Exception $e) {
            Log::error('Google callback failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'query_params' => $request->query(),
            ]);
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect("{$frontendUrl}/auth?error=" . urlencode('Google authentication failed: ' . $e->getMessage()));
        }
    }

    /**
     * Signup new user with Google OAuth
     */
    public function googleSignup(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'google_token' => 'required|string',
                'phone_number' => 'nullable|string|max:20',
                'community_name' => 'required|string|max:255',
            ]);

            // Validate Google token
            $googleData = Cache::get("google_signup:{$validated['google_token']}");
            if (!$googleData) {
                return response()->json(['error' => 'Invalid or expired Google token'], 400);
            }

            $email = $googleData['email'];

            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                return response()->json(['error' => 'User already exists'], 400);
            }

            // Check if community already exists
            $existingTenant = Tenant::where('name', $validated['community_name'])->first();
            if ($existingTenant) {
                return response()->json(['error' => 'Community already exists'], 400);
            }

            DB::beginTransaction();

            try {
                // Create tenant
                $tenant = Tenant::create([
                    'name' => $validated['community_name'],
                    'slug' => Str::slug($validated['community_name']),
                    'trial_ends_at' => now()->addDays(30),
                    'is_active' => true,
                ]);

                // Create user
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $googleData['name'],
                    'email' => $email,
                    'phone_number' => $validated['phone_number'] ?? null,
                    'avatar_url' => $googleData['avatar'] ?? null,
                    'role' => 'admin',
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

                // Generate Sanctum token
                $token = $user->createToken('auth-token')->plainTextToken;

                // Delete Google token from cache
                Cache::forget("google_signup:{$validated['google_token']}");

                DB::commit();

                return response()->json([
                    'message' => 'Account created successfully with Google',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone_number' => $user->phone_number,
                        'avatar_url' => $user->avatar_url,
                        'email_verified' => true,
                    ],
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'trial_ends_at' => $tenant->trial_ends_at,
                    ],
                    'token' => $token,
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Google signup failed: ' . $e->getMessage());
            return response()->json(['error' => 'Google signup failed'], 500);
        }
    }

    /**
     * Authenticate user with magic link token
     */
    public function magicLinkAuth(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
            ]);

            $token = $validated['token'];
            
            // Get magic link data from cache
            $magicLinkData = Cache::get("magic_link:{$token}");
            
            if (!$magicLinkData) {
                // Magic link expired or invalid
                // Email should be in URL query parameter, but we can't access it here
                // Frontend will extract it from URL
                return response()->json([
                    'error' => 'Invalid or expired magic link',
                ], 400);
            }

            $email = $magicLinkData['email'];
            $residentId = $magicLinkData['resident_id'] ?? null;

            // Find user
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'User not found'
                ], 404);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'error' => 'Account is inactive'
                ], 403);
            }

            // Verify resident ID matches if provided
            if ($residentId && $user->id != $residentId) {
                return response()->json([
                    'error' => 'Invalid magic link'
                ], 400);
            }

            // Update last login
            $user->updateLastLogin();

            // Generate Sanctum token
            $authToken = $user->createToken('magic-link-auth')->plainTextToken;

            // Delete magic link token from cache (one-time use)
            Cache::forget("magic_link:{$token}");

            $user->load('tenant');

            return response()->json([
                'message' => 'Authentication successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone_number' => $user->phone_number,
                    'email_verified' => $user->email_verified_at !== null,
                ],
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'slug' => $user->tenant->slug,
                    'trial_ends_at' => $user->tenant->trial_ends_at,
                ] : null,
                'token' => $authToken,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Magic link auth failed: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed'], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->updateLastLogin();
            $user->load('tenant');

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone_number' => $user->phone_number,
                    'avatar_url' => $user->avatar_url,
                    'email_verified' => $user->email_verified_at !== null,
                    'is_active' => $user->is_active,
                    'last_login_at' => $user->last_login_at,
                ],
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'slug' => $user->tenant->slug,
                    'is_active' => $user->tenant->is_active,
                    'trial_ends_at' => $user->tenant->trial_ends_at,
                    'subscription_ends_at' => $user->tenant->subscription_ends_at,
                    'can_access' => $user->tenant->canAccess(),
                ] : null
            ]);

        } catch (\Exception $e) {
            Log::error('Get user info failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get user information'], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user) {
                // Revoke current token
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json(['message' => 'Logged out successfully']);

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }
}
