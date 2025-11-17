# PostHog Implementation Guide for neibrPay

## Table of Contents

1. Landing Page Setup (Nuxt.js)
2. Web App Setup (Vue 3 + Laravel)
3. Custom Events to Track
4. Backend Integration (Laravel)
5. Advanced Features

---

## 1. Landing Page Setup (Nuxt.js at neibrpay.com)

### Installation

```bash
npm install posthog-js
```

### Option A: Using the Official Nuxt Module (Recommended for Nuxt 3.7+)

```bash
npx nuxi@latest module add nuxt-posthog
```

Add to `nuxt.config.ts`:

```typescript
export default defineNuxtConfig({
  modules: ['nuxt-posthog'],
  posthog: {
    apiKey: 'your-posthog-api-key',
    host: 'https://us.i.posthog.com', // or EU
    scriptUrl: '/array.js', // Optional: for self-hosted
  },
});
```

### Option B: Manual Plugin Setup (For More Control)

Create `plugins/posthog.client.js`:

```javascript
import { defineNuxtPlugin } from '#app';
import posthog from 'posthog-js';

export default defineNuxtPlugin(nuxtApp => {
  const router = useRouter();

  const posthogInstance = posthog.init('your-posthog-api-key', {
    api_host: 'https://us.i.posthog.com',
    autocapture: true,
    capture_pageleave: true,
  });

  // Track page views on route changes
  router.afterEach(to => {
    posthog.capture('$pageview', {
      $current_url: window.location.href,
      page_name: to.name,
    });
  });

  return {
    provide: {
      posthog: posthogInstance,
    },
  };
});
```

### Tracking Custom Events on Landing Page

Add to your landing page components (e.g., `components/DemoButton.vue`):

```vue
<template>
  <button @click="trackDemoClick" class="btn-primary">Book a Demo</button>
</template>

<script setup>
const { $posthog } = useNuxtApp();

const trackDemoClick = () => {
  $posthog.capture('demo_button_clicked', {
    page: 'landing_page',
    timestamp: new Date().toISOString(),
  });
  // Navigate to demo form or external link
};
</script>
```

### Track "Get Started" Button

```javascript
const trackGetStarted = () => {
  $posthog.capture('get_started_clicked', {
    page: 'landing_page',
    button_text: 'Get Started',
  });
};
```

---

## 2. Web App Setup (Vue 3 + Laravel at app.neibrpay.com)

### Frontend Installation

```bash
npm install posthog-js
```

### Create Composable: `composables/usePosthog.ts`

```typescript
import { useRouter } from 'vue-router';
import posthog from 'posthog-js';

export const usePosthog = () => {
  const router = useRouter();

  // Initialize PostHog
  const init = () => {
    posthog.init('your-posthog-api-key', {
      api_host: 'https://us.i.posthog.com',
      autocapture: true,
      session_recording: {
        recordCrossOriginIframes: false,
      },
    });

    // Track page views
    router.afterEach(to => {
      posthog.capture('$pageview', {
        $current_url: window.location.href,
        page_name: to.name,
      });
    });
  };

  const identifyUser = (userId: string, userProperties: any) => {
    posthog.identify(userId, {
      email: userProperties.email,
      name: userProperties.name,
      unit_id: userProperties.unit_id,
      is_admin: userProperties.is_admin,
      signup_date: userProperties.created_at,
    });
  };

  const captureEvent = (eventName: string, properties: any = {}) => {
    posthog.capture(eventName, properties);
  };

  const resetUser = () => {
    posthog.reset();
  };

  return {
    init,
    identifyUser,
    captureEvent,
    resetUser,
  };
};
```

### Setup in `main.ts`

```typescript
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { usePosthog } from './composables/usePosthog';

const app = createApp(App);

app.use(router);
app.mount('#app');

// Initialize PostHog after app mount
const { init } = usePosthog();
init();
```

### Track User Login

In your login component:

```vue
<script setup>
import { usePosthog } from '@/composables/usePosthog'

const { captureEvent, identifyUser } = usePosthog()

const handleLogin = async (email: string, password: string) => {
  try {
    const response = await loginAPI(email, password)
    const user = response.data.user

    // Identify user in PostHog
    identifyUser(user.id, {
      email: user.email,
      name: user.name,
      unit_id: user.unit_id,
      is_admin: user.role === 'admin',
      created_at: user.created_at,
    })

    // Track login event
    captureEvent('user_login', {
      email: user.email,
      user_type: user.role,
    })

    // Navigate to dashboard
    await router.push('/dashboard')
  } catch (error) {
    captureEvent('login_failed', {
      error: error.message,
    })
  }
}
</script>
```

### Track Invoice Actions

```typescript
// In invoice management component
const handleInvoiceCreated = (invoice: any) => {
  captureEvent('invoice_created', {
    invoice_id: invoice.id,
    amount: invoice.total_amount,
    unit_id: invoice.unit_id,
    currency: invoice.currency,
  });
};

const handleInvoicePaid = (invoice: any) => {
  captureEvent('invoice_paid', {
    invoice_id: invoice.id,
    amount: invoice.total_amount,
    payment_method: invoice.payment_method,
    days_to_payment: invoice.days_to_payment,
  });
};

const handlePaymentReminderSent = (invoice: any, reminderCount: number) => {
  captureEvent('payment_reminder_sent', {
    invoice_id: invoice.id,
    reminder_number: reminderCount,
    recipient_email: invoice.owner_email,
  });
};
```

---

## 3. Custom Events to Track

### User Journey Events

```javascript
// Onboarding
$posthog.capture('onboarding_started');
$posthog.capture('unit_added', { unit_count: 5 });
$posthog.capture('onboarding_completed');

// Account Management
$posthog.capture('profile_updated', { fields_updated: ['name', 'email'] });
$posthog.capture('account_settings_opened');
$posthog.capture('two_factor_enabled');

// Payments
$posthog.capture('payment_initiated', {
  amount: 500,
  method: 'stripe',
  currency: 'USD',
});
$posthog.capture('payment_failed', {
  reason: 'declined',
  error_code: 'card_declined',
});
```

### Vendor Management Events

```javascript
$posthog.capture('vendor_added', { vendor_category: 'plumbing' });
$posthog.capture('vendor_contact_viewed', { vendor_id: 123 });
$posthog.capture('vendor_document_uploaded', { file_type: 'contract' });
```

### Feature Engagement

```javascript
$posthog.capture('document_downloaded', {
  document_type: 'financial_report',
  date_range: 'Q4_2024',
});
$posthog.capture('announcement_sent', {
  recipient_count: 45,
  delivery_method: 'email',
});
```

---

## 4. Backend Integration (Laravel)

### Installation

```bash
composer require posthog/posthog-php
```

### Configuration: `config/posthog.php`

```php
return [
    'api_key' => env('POSTHOG_API_KEY'),
    'host' => env('POSTHOG_HOST', 'https://us.i.posthog.com'),
];
```

### Add to `.env`

```
POSTHOG_API_KEY=your-posthog-api-key
POSTHOG_HOST=https://us.i.posthog.com
```

### Create Service: `app/Services/AnalyticsService.php`

```php
<?php

namespace App\Services;

use PostHog\PostHog;

class AnalyticsService
{
    protected PostHog $posthog;

    public function __construct()
    {
        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host')]
        );
        $this->posthog = new PostHog();
    }

    public function captureEvent(string $event, string $distinctId, array $properties = []): void
    {
        $this->posthog->capture([
            'distinctId' => $distinctId,
            'event' => $event,
            'properties' => $properties,
        ]);
    }

    public function identifyUser(string $userId, array $properties = []): void
    {
        $this->posthog->identify([
            'distinctId' => $userId,
            'properties' => $properties,
        ]);
    }

    public function aliasUser(string $previousId, string $newId): void
    {
        $this->posthog->alias([
            'distinctId' => $newId,
            'alias' => $previousId,
        ]);
    }
}
```

### Track Events in Controllers

```php
<?php

namespace App\Http\Controllers\API;

use App\Services\AnalyticsService;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function store(Request $request)
    {
        $invoice = Invoice::create($request->validated());

        $this->analytics->captureEvent(
            'invoice_created_backend',
            auth()->id(),
            [
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount,
                'unit_count' => $invoice->units()->count(),
                'source' => 'api',
            ]
        );

        return response()->json($invoice, 201);
    }

    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        $this->analytics->captureEvent(
            'invoice_marked_paid',
            auth()->id(),
            [
                'invoice_id' => $invoice->id,
                'payment_method' => $request->payment_method,
                'days_outstanding' => $invoice->created_at->diffInDays(now()),
            ]
        );

        return response()->json($invoice);
    }
}
```

### Track User Registration (UserController)

```php
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    // Identify user in PostHog
    $this->analytics->identifyUser($user->id, [
        'email' => $user->email,
        'name' => $user->name,
        'signup_date' => $user->created_at,
        'hoa_name' => $request->hoa_name,
    ]);

    // Track signup event
    $this->analytics->captureEvent(
        'user_registered',
        $user->id,
        [
            'email' => $user->email,
            'plan_type' => $request->plan ?? 'starter',
        ]
    );

    return response()->json(['user' => $user], 201);
}
```

### Middleware for User Identification

```php
<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsService;
use Closure;

class IdentifyPosthogUser
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $analytics = app(AnalyticsService::class);

            $analytics->identifyUser($user->id, [
                'email' => $user->email,
                'name' => $user->name,
                'is_admin' => $user->is_admin,
            ]);
        }

        return $next($request);
    }
}
```

Add to `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ... other middleware
    \App\Http\Middleware\IdentifyPosthogUser::class,
];
```

---

## 5. Advanced Features

### Session Recording Filtering

For landing page (Nuxt):

```javascript
posthog.init('your-key', {
  // Only record sessions on specific pages
  session_recording: {
    maskAllInputs: true,
    maskAllText: false,
  },
  // Only record 10% of sessions to reduce costs
  sample_rate: 0.1,
});
```

### Feature Flags (A/B Testing)

```javascript
// Check if user should see new feature
const isNewPaymentFlowEnabled = posthog.isFeatureEnabled('new-payment-flow');

if (isNewPaymentFlowEnabled) {
  // Show new payment interface
} else {
  // Show legacy payment interface
}
```

### User Cohorts for Targeted Campaigns

Via PostHog Dashboard:

1. Go to Cohorts
2. Create cohort: "High-value HOAs" (invoices > $1000/month)
3. Create cohort: "Trial Users" (signup within 14 days)
4. Use cohorts to target in-app messages or feature flags

---

## Tracking Checklist

- [x] Landing page pageviews
- [x] Demo button clicks
- [x] Get started clicks
- [x] User registration/login
- [x] Invoice creation
- [x] Invoice payments
- [x] Payment failures
- [x] Vendor management actions
- [x] Document uploads/downloads
- [x] Account settings updates
- [x] Feature flag exposures
- [x] Session recordings (optional)
- [x] Error tracking
