<?php

namespace App\Support;

final class TenantTimezone
{
    /**
     * Normalize stored settings (e.g. "America/New_York(EST)") to a valid PHP timezone id.
     */
    public static function normalize(?string $value, string $fallback = 'UTC'): string
    {
        if ($value === null || trim($value) === '') {
            return $fallback;
        }

        $trimmed = trim($value);
        $identifiers = timezone_identifiers_list();

        if (preg_match('/^([A-Za-z0-9_\/+-]+)/', $trimmed, $m)) {
            $candidate = $m[1];
            if (in_array($candidate, $identifiers, true)) {
                return $candidate;
            }
        }

        if (in_array($trimmed, $identifiers, true)) {
            return $trimmed;
        }

        return $fallback;
    }
}
