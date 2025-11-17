<?php

namespace App\Services;

use PostHog\PostHog;

class AnalyticsService
{
    protected bool $enabled;
    protected string $apiKey;
    protected string $host;

    public function __construct()
    {
        $this->enabled = config('posthog.enabled', true);
        $this->apiKey = config('posthog.api_key');
        $this->host = config('posthog.host', 'https://us.i.posthog.com');

        if ($this->enabled && $this->apiKey) {
            PostHog::init($this->apiKey, [
                'host' => $this->host,
            ]);
        }
    }

    /**
     * Capture an event
     *
     * @param string $event Event name
     * @param string|int $distinctId User ID or distinct identifier
     * @param array $properties Event properties
     * @return void
     */
    public function captureEvent(string $event, string|int $distinctId, array $properties = []): void
    {
        if (!$this->enabled || !$this->apiKey) {
            return;
        }

        try {
            PostHog::capture([
                'distinctId' => (string) $distinctId,
                'event' => $event,
                'properties' => array_merge($properties, [
                    'source' => 'backend',
                    'timestamp' => now()->toIso8601String(),
                ]),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the application
            \Log::warning('PostHog event capture failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Identify a user
     *
     * @param string|int $userId User ID
     * @param array $properties User properties
     * @return void
     */
    public function identifyUser(string|int $userId, array $properties = []): void
    {
        if (!$this->enabled || !$this->apiKey) {
            return;
        }

        try {
            PostHog::identify([
                'distinctId' => (string) $userId,
                'properties' => $properties,
            ]);
        } catch (\Exception $e) {
            \Log::warning('PostHog user identification failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Alias a user (for connecting anonymous and identified users)
     *
     * @param string|int $previousId Previous user ID
     * @param string|int $newId New user ID
     * @return void
     */
    public function aliasUser(string|int $previousId, string|int $newId): void
    {
        if (!$this->enabled || !$this->apiKey) {
            return;
        }

        try {
            PostHog::alias([
                'distinctId' => (string) $newId,
                'alias' => (string) $previousId,
            ]);
        } catch (\Exception $e) {
            \Log::warning('PostHog user alias failed', [
                'previous_id' => $previousId,
                'new_id' => $newId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Shutdown PostHog (flush pending events)
     *
     * @return void
     */
    public function shutdown(): void
    {
        if ($this->enabled && $this->apiKey) {
            try {
                PostHog::shutdown();
            } catch (\Exception $e) {
                \Log::warning('PostHog shutdown failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

