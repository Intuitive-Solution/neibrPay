<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuth
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get the Authorization header
            $authHeader = $request->header('Authorization');
            
            if (!$authHeader) {
                return response()->json([
                    'error' => 'Authorization header missing',
                    'message' => 'Bearer token is required'
                ], 401);
            }

            // Extract the token from "Bearer <token>"
            if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return response()->json([
                    'error' => 'Invalid authorization header format',
                    'message' => 'Authorization header must be in format: Bearer <token>'
                ], 401);
            }

            $idToken = $matches[1];
            

            // Verify the Firebase ID token
            $tokenData = $this->firebaseService->verifyIdToken($idToken);
            
            // Find or create user in our database
            $user = $this->getOrCreateUser($tokenData);
            
            if (!$user) {
                return response()->json([
                    'error' => 'User not found',
                    'message' => 'User account not found in system'
                ], 404);
            }

            // Set the authenticated user in the Auth system
            Auth::setUser($user);
            
            // Add user to request for use in controllers
            $request->merge(['firebase_user' => $user]);
            $request->merge(['firebase_token_data' => $tokenData]);
            
            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Get or create user from Firebase token data
     *
     * @param array $tokenData
     * @return User|null
     */
    private function getOrCreateUser(array $tokenData): ?User
    {
        $firebaseUid = $tokenData['uid'];
        
        // Try to find existing user by Firebase UID
        $user = User::where('firebase_uid', $firebaseUid)->first();
        
        if ($user) {
            // Update user data if needed
            $this->updateUserFromTokenData($user, $tokenData);
            return $user;
        }

        // If user doesn't exist, we can't create them here
        // This should only happen during signup process
        return null;
    }

    /**
     * Update user data from Firebase token
     *
     * @param User $user
     * @param array $tokenData
     * @return void
     */
    private function updateUserFromTokenData(User $user, array $tokenData): void
    {
        $updated = false;

        // Update email if changed
        if ($user->email !== $tokenData['email']) {
            $user->email = $tokenData['email'];
            $updated = true;
        }

        // Update name if changed and available
        if ($tokenData['name'] && $user->name !== $tokenData['name']) {
            $user->name = $tokenData['name'];
            $updated = true;
        }

        // Update email verification status
        if ($user->email_verified_at === null && $tokenData['email_verified']) {
            $user->email_verified_at = now();
            $updated = true;
        }

        // Update phone number if available and changed
        if ($tokenData['phone_number'] && $user->phone_number !== $tokenData['phone_number']) {
            $user->phone_number = $tokenData['phone_number'];
            $updated = true;
        }

        if ($updated) {
            $user->save();
        }
    }
}
