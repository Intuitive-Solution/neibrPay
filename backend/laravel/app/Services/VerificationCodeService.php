<?php

namespace App\Services;

use App\Models\VerificationCode;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use Carbon\Carbon;

class VerificationCodeService
{
    /**
     * Code expiration time in minutes.
     */
    private const EXPIRATION_MINUTES = 15;

    /**
     * Maximum number of codes per email per hour for new users.
     */
    private const MAX_CODES_PER_HOUR_NEW = 3;

    /**
     * Maximum number of codes per email per hour for existing users.
     */
    private const MAX_CODES_PER_HOUR_EXISTING = 10;

    /**
     * Generate a random 6-digit code.
     */
    private function generateRandomCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if we can send a code for the given email (rate limiting).
     *
     * @param string $email
     * @param bool $isExistingUser Whether the user already exists in the system
     * @return bool
     */
    public function canSendCode(string $email, bool $isExistingUser = false): bool
    {
        $oneHourAgo = now()->subHour();
        
        $recentCodes = VerificationCode::forEmail($email)
            ->where('created_at', '>=', $oneHourAgo)
            ->count();

        $maxCodes = $isExistingUser ? self::MAX_CODES_PER_HOUR_EXISTING : self::MAX_CODES_PER_HOUR_NEW;

        return $recentCodes < $maxCodes;
    }

    /**
     * Generate and store a verification code.
     *
     * @param string $email
     * @param int|null $tenantId
     * @param string|null $ipAddress
     * @return VerificationCode
     * @throws Exception
     */
    public function generateCode(string $email, ?int $tenantId = null, ?string $ipAddress = null, bool $isExistingUser = false): VerificationCode
    {
        // Check rate limits
        if (!$this->canSendCode($email, $isExistingUser)) {
            $maxCodes = $isExistingUser ? self::MAX_CODES_PER_HOUR_EXISTING : self::MAX_CODES_PER_HOUR_NEW;
            throw new Exception("Too many verification code requests. Maximum {$maxCodes} codes per hour allowed. Please try again later.");
        }

        // Invalidate any existing unverified codes for this email
        VerificationCode::forEmail($email)
            ->valid()
            ->update(['verified_at' => now()]);

        // Generate new code
        $code = $this->generateRandomCode();
        $expiresAt = now()->addMinutes(self::EXPIRATION_MINUTES);

        $verificationCode = VerificationCode::create([
            'email' => $email,
            'code' => $code,
            'tenant_id' => $tenantId,
            'expires_at' => $expiresAt,
            'ip_address' => $ipAddress,
        ]);

        return $verificationCode;
    }

    /**
     * Validate a verification code.
     *
     * @param string $email
     * @param string $code
     * @return VerificationCode|null
     */
    public function validateCode(string $email, string $code): ?VerificationCode
    {
        $verificationCode = VerificationCode::forEmail($email)
            ->forCode($code)
            ->valid()
            ->first();

        if (!$verificationCode) {
            return null;
        }

        // Mark as verified
        $verificationCode->markAsVerified();

        return $verificationCode;
    }

    /**
     * Send verification code email via n8n webhook.
     *
     * @param string $email
     * @param string $code
     * @param string|null $userName
     * @param string|null $tenantName
     * @return void
     */
    public function sendCodeEmail(string $email, string $code, ?string $userName = null, ?string $tenantName = null): void
    {
        $webhookUrl = config('n8n.verification_code_webhook_url') ?? config('n8n.webhook_url');
        
        if (!$webhookUrl) {
            Log::warning('N8N webhook URL not configured. Verification code email not sent.', [
                'email' => $email,
                'code' => $code,
            ]);
            return;
        }

        try {
            $response = Http::timeout(10)->post($webhookUrl, [
                'type' => 'verification_code',
                'to' => $email, // Email recipient field (standard for email nodes)
                'email' => $email, // Also include for backward compatibility
                'recipient' => $email, // Alternative field name
                'code' => $code,
                'user_name' => $userName,
                'tenant_name' => $tenantName,
                'expires_in_minutes' => self::EXPIRATION_MINUTES,
            ]);

            if (!$response->successful()) {
                Log::error('Failed to send verification code email via n8n', [
                    'email' => $email,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error sending verification code email via n8n', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clean up expired verification codes.
     * This should be run periodically via a scheduled task.
     *
     * @return int Number of codes deleted
     */
    public function cleanupExpiredCodes(): int
    {
        $deleted = VerificationCode::expired()
            ->whereNull('verified_at')
            ->delete();

        return $deleted;
    }

    /**
     * Get the expiration time in seconds.
     */
    public function getExpirationSeconds(): int
    {
        return self::EXPIRATION_MINUTES * 60;
    }
}

