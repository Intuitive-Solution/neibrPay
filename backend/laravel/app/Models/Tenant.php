<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'website',
        'settings',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }
        });

        static::updating(function ($tenant) {
            if ($tenant->isDirty('name') && empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }
        });
    }

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the admin users for the tenant.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    /**
     * Check if tenant is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    /**
     * Check if tenant can access the system.
     */
    public function canAccess(): bool
    {
        return $this->is_active && ($this->isOnTrial() || $this->hasActiveSubscription());
    }

    /**
     * Get tenant setting.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set tenant setting.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Check if PayPal is enabled for this tenant.
     */
    public function getPayPalEnabled(): bool
    {
        return (bool) data_get($this->settings, 'paypal.enabled', false);
    }

    /**
     * Get PayPal configuration for this tenant.
     */
    public function getPayPalConfig(): ?array
    {
        $paypalConfig = data_get($this->settings, 'paypal');
        
        if (!$paypalConfig || !isset($paypalConfig['enabled']) || !$paypalConfig['enabled']) {
            return null;
        }

        return [
            'enabled' => (bool) ($paypalConfig['enabled'] ?? false),
            'client_id' => $paypalConfig['client_id'] ?? null,
            'client_secret' => $paypalConfig['client_secret'] ?? null,
            'mode' => $paypalConfig['mode'] ?? 'sandbox',
            'webhook_id' => $paypalConfig['webhook_id'] ?? null,
        ];
    }

    /**
     * Set PayPal configuration for this tenant.
     */
    public function setPayPalConfig(array $config): void
    {
        $settings = $this->settings ?? [];
        $settings['paypal'] = $config;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Check if Stripe is enabled for this tenant.
     */
    public function getStripeEnabled(): bool
    {
        return (bool) data_get($this->settings, 'stripe.enabled', false);
    }

    /**
     * Get Stripe configuration for this tenant.
     */
    public function getStripeConfig(): ?array
    {
        $stripeConfig = data_get($this->settings, 'stripe');
        
        if (!$stripeConfig || !isset($stripeConfig['enabled']) || !$stripeConfig['enabled']) {
            return null;
        }

        return [
            'enabled' => (bool) ($stripeConfig['enabled'] ?? false),
            'key' => $stripeConfig['key'] ?? null,
            'secret' => $stripeConfig['secret'] ?? null,
            'webhook_secret' => $stripeConfig['webhook_secret'] ?? null,
        ];
    }

    /**
     * Set Stripe configuration for this tenant.
     */
    public function setStripeConfig(array $config): void
    {
        $settings = $this->settings ?? [];
        $settings['stripe'] = $config;
        $this->settings = $settings;
        $this->save();
    }
}
