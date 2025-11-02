<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle email/password signup
     */
    public function signup(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'community_name' => 'required|string|max:255',
                'full_name' => 'required|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'firebase_uid' => 'required|string',
                'email' => 'required|email|max:255',
            ]);

            $authHeader = $request->header('Authorization');
            if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return response()->json(['error' => 'Invalid authorization header'], 401);
            }

            $tokenData = $this->firebaseService->verifyIdToken($matches[1]);

            if ($tokenData['uid'] !== $validated['firebase_uid']) {
                return response()->json(['error' => 'Token mismatch'], 401);
            }

            $existingUser = User::where('firebase_uid', $validated['firebase_uid'])->first();
            if ($existingUser) {
                return response()->json(['error' => 'User already exists'], 400);
            }

            $existingTenant = Tenant::where('name', $validated['community_name'])->first();
            if ($existingTenant) {
                return response()->json(['error' => 'Community already exists'], 400);
            }

            DB::beginTransaction();

            try {
                $tenant = Tenant::create([
                    'name' => $validated['community_name'],
                    'slug' => Str::slug($validated['community_name']),
                    'trial_ends_at' => now()->addDays(30),
                ]);

                $user = User::create([
                    'firebase_uid' => $validated['firebase_uid'],
                    'tenant_id' => $tenant->id,
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'role' => 'admin',
                    'email_verified_at' => $tokenData['email_verified'] ? now() : null,
                    'is_active' => true,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Account created successfully',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone_number' => $user->phone_number,
                        'email_verified' => $user->email_verified_at !== null,
                    ],
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'trial_ends_at' => $tenant->trial_ends_at,
                    ]
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
     * Handle Google signup
     */
    public function googleSignup(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'community_name' => 'required|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'firebase_uid' => 'required|string',
                'email' => 'required|email|max:255',
                'full_name' => 'required|string|max:255',
            ]);

            $authHeader = $request->header('Authorization');
            if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return response()->json(['error' => 'Invalid authorization header'], 401);
            }

            $tokenData = $this->firebaseService->verifyIdToken($matches[1]);

            if ($tokenData['uid'] !== $validated['firebase_uid']) {
                return response()->json(['error' => 'Token mismatch'], 401);
            }

            if (!$this->firebaseService->isGoogleProvider($tokenData)) {
                return response()->json(['error' => 'Invalid provider'], 400);
            }

            $existingUser = User::where('firebase_uid', $validated['firebase_uid'])->first();
            if ($existingUser) {
                return response()->json(['error' => 'User already exists'], 400);
            }

            $existingTenant = Tenant::where('name', $validated['community_name'])->first();
            if ($existingTenant) {
                return response()->json(['error' => 'Community already exists'], 400);
            }

            DB::beginTransaction();

            try {
                $tenant = Tenant::create([
                    'name' => $validated['community_name'],
                    'slug' => Str::slug($validated['community_name']),
                    'trial_ends_at' => now()->addDays(30),
                ]);

                $user = User::create([
                    'firebase_uid' => $validated['firebase_uid'],
                    'tenant_id' => $tenant->id,
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'avatar_url' => $tokenData['picture'] ?? null,
                    'role' => 'admin',
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

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
                    ]
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
     * Get current user and tenant information
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->get('firebase_user');

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
            $tokenData = $request->get('firebase_token_data');

            if ($tokenData) {
                $this->firebaseService->revokeRefreshTokens($tokenData['uid']);
            }

            return response()->json(['message' => 'Logged out successfully']);

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255',
                'phone_number' => 'sometimes|nullable|string|max:20',
            ]);

            $user = $request->get('firebase_user');

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Update only provided fields
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (isset($validated['phone_number'])) {
                $user->phone_number = $validated['phone_number'];
            }

            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number ?? '',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update profile failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update profile'], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string',
            ]);

            $user = $request->get('firebase_user');
            $tokenData = $request->get('firebase_token_data');

            if (!$user || !$tokenData) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $uid = $tokenData['uid'];

            // Verify current password by attempting to sign in
            try {
                // Firebase Admin SDK doesn't have a direct way to verify password
                // We'll update the password directly. The frontend should verify current password
                // by re-authenticating the user before calling this endpoint
                $this->firebaseService->updateUserPassword($uid, $validated['new_password']);
            } catch (\Exception $e) {
                Log::error('Password update failed: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to update password. Please ensure your current password is correct.'], 400);
            }

            return response()->json([
                'message' => 'Password updated successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Change password failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to change password'], 500);
        }
    }
}
