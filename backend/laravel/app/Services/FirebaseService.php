<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\InvalidToken;
use Exception;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private Auth $auth;
    private string $projectId;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');
        $this->initializeFirebase();
    }

    /**
     * Initialize Firebase Admin SDK
     */
    private function initializeFirebase(): void
    {
        try {
            $credentialsJson = config('firebase.credentials_json');
            $credentialsPath = config('firebase.credentials_path');
            
            $factory = new Factory();
            
            // Use JSON credentials if available (Railway environment)
            if ($credentialsJson) {
                $credentials = json_decode($credentialsJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in FIREBASE_CREDENTIALS_JSON');
                }
                $factory = $factory->withServiceAccount($credentials);
            } 
            // Use Base64 encoded credentials if available (Railway environment)
            elseif (config('firebase.credentials_base64')) {
                $credentialsJson = base64_decode(config('firebase.credentials_base64'));
                $credentials = json_decode($credentialsJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in FIREBASE_CREDENTIALS_BASE64');
                }
                $factory = $factory->withServiceAccount($credentials);
            }
            // Fallback to file path (local development)
            elseif (file_exists($credentialsPath)) {
                $factory = $factory->withServiceAccount($credentialsPath);
            } else {
                throw new Exception("Firebase credentials not found. Please set FIREBASE_CREDENTIALS_JSON, FIREBASE_CREDENTIALS_BASE64, or ensure file exists at: {$credentialsPath}");
            }

            $factory = $factory->withProjectId($this->projectId);
            $this->auth = $factory->createAuth();
        } catch (Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            throw new Exception('Firebase service initialization failed');
        }
    }

    /**
     * Verify Firebase ID token
     *
     * @param string $idToken
     * @return array
     * @throws Exception
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            $claims = $verifiedToken->claims();
            
            return [
                'uid' => $claims->get('sub'),
                'email' => $claims->get('email'),
                'email_verified' => $claims->get('email_verified', false),
                'name' => $claims->get('name'),
                'picture' => $claims->get('picture'),
                'phone_number' => $claims->get('phone_number'),
                'firebase' => [
                    'identities' => $claims->get('firebase', [])['identities'] ?? [],
                    'sign_in_provider' => $claims->get('firebase', [])['sign_in_provider'] ?? null,
                ],
                'iat' => $claims->get('iat'),
                'exp' => $claims->get('exp'),
            ];
        } catch (FailedToVerifyToken $e) {
            Log::warning('Failed to verify Firebase token: ' . $e->getMessage());
            throw new Exception('Invalid authentication token');
        } catch (InvalidToken $e) {
            Log::warning('Invalid Firebase token: ' . $e->getMessage());
            throw new Exception('Invalid authentication token');
        } catch (Exception $e) {
            Log::error('Firebase token verification error: ' . $e->getMessage());
            throw new Exception('Token verification failed');
        }
    }

    /**
     * Get user by Firebase UID
     *
     * @param string $uid
     * @return array|null
     */
    public function getUserByUid(string $uid): ?array
    {
        try {
            $userRecord = $this->auth->getUser($uid);
            
            return [
                'uid' => $userRecord->uid,
                'email' => $userRecord->email,
                'email_verified' => $userRecord->emailVerified,
                'display_name' => $userRecord->displayName,
                'photo_url' => $userRecord->photoUrl,
                'phone_number' => $userRecord->phoneNumber,
                'disabled' => $userRecord->disabled,
                'metadata' => [
                    'created_at' => $userRecord->metadata->createdAt,
                    'last_sign_in_at' => $userRecord->metadata->lastSignInAt,
                ],
                'provider_data' => $userRecord->providerData,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get Firebase user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create custom token for a user
     *
     * @param string $uid
     * @param array $additionalClaims
     * @return string
     */
    public function createCustomToken(string $uid, array $additionalClaims = []): string
    {
        try {
            return $this->auth->createCustomToken($uid, $additionalClaims);
        } catch (Exception $e) {
            Log::error('Failed to create custom token: ' . $e->getMessage());
            throw new Exception('Failed to create custom token');
        }
    }

    /**
     * Revoke all refresh tokens for a user
     *
     * @param string $uid
     * @return void
     */
    public function revokeRefreshTokens(string $uid): void
    {
        try {
            $this->auth->revokeRefreshTokens($uid);
        } catch (Exception $e) {
            Log::error('Failed to revoke refresh tokens: ' . $e->getMessage());
            throw new Exception('Failed to revoke refresh tokens');
        }
    }

    /**
     * Check if token is from Google provider
     *
     * @param array $tokenData
     * @return bool
     */
    public function isGoogleProvider(array $tokenData): bool
    {
        $signInProvider = $tokenData['firebase']['sign_in_provider'] ?? null;
        $identities = $tokenData['firebase']['identities'] ?? [];
        
        return $signInProvider === 'google.com' || 
               isset($identities['google.com']) || 
               isset($identities['google']);
    }

    /**
     * Check if token is from email/password provider
     *
     * @param array $tokenData
     * @return bool
     */
    public function isEmailPasswordProvider(array $tokenData): bool
    {
        $signInProvider = $tokenData['firebase']['sign_in_provider'] ?? null;
        $identities = $tokenData['firebase']['identities'] ?? [];
        
        return $signInProvider === 'password' || 
               isset($identities['email']) ||
               isset($identities['password']);
    }

    /**
     * Update user password in Firebase
     *
     * @param string $uid
     * @param string $newPassword
     * @return void
     * @throws Exception
     */
    public function updateUserPassword(string $uid, string $newPassword): void
    {
        try {
            $this->auth->updateUser($uid, [
                'password' => $newPassword,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update user password: ' . $e->getMessage());
            throw new Exception('Failed to update password');
        }
    }
}
