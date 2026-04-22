// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  devServer: {
    port: 3002,
  },
  modules: ['@nuxtjs/tailwindcss', '@nuxtjs/google-fonts'],
  googleFonts: {
    families: {
      Inter: [400, 500, 600, 700],
    },
    display: 'swap',
    preload: true,
  },
  css: ['~/assets/css/main.css'],
  app: {
    head: {
      title: 'NeibrPay — HOA Dues Collection & Community Management Platform',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content:
            'Collect HOA dues online via Zelle, ACH, or card. Manage vendors, store documents, and communicate with residents. Built for self-managed HOAs.',
        },
      ],
      link: [{ rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' }],
    },
  },
  nitro: {
    // Static export (`nuxt generate`) cannot deploy server routes — `/api/contact` would 404.
    // On Netlify, use the netlify preset + `nuxt build` so API handlers become serverless functions.
    // Local `nuxt generate` / non-Netlify builds keep `static` (avoids netlify output dirs during generate).
    preset: process.env.NETLIFY ? 'netlify' : 'static',
    prerender: {
      routes: [
        '/sitemap.xml',
        '/about',
        '/contact',
        '/support',
        '/privacy',
        '/terms',
        '/get-started',
        '/features/invoice-payment-management',
        '/features/vendor-expense-tracking',
        '/features/budgets-and-reports',
        '/features/bank-reconciliation',
        '/features/reserve-fund-management',
        '/features/violations',
        '/features/architectural-requests',
        '/features/online-voting-polls',
        '/features/meeting-management',
        '/features/events-calendar',
        '/features/document-storage',
        '/features/owner-portal',
        '/features/announcements-communication',
        '/features/multi-tenant-architecture',
      ],
    },
  },
  runtimeConfig: {
    public: {
      /** Canonical site origin for sitemap URLs (e.g. https://neibrpay.com). */
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://neibrpay.com',
      adminWebUrl: process.env.ADMIN_WEB_URL || 'http://localhost:3000',
      calendlyUrl:
        process.env.CALENDLY_URL ||
        'https://calendly.com/imailtahir/neibrpay-demo',
      posthogKey: process.env.POSTHOG_KEY || '',
      /** Reverse proxy / custom ingest domain (see PostHog project settings). */
      posthogHost: process.env.POSTHOG_HOST || 'https://j.neibrpay.com',
    },
    // Server-side only (not exposed to client)
    smtpHost: process.env.SMTP_HOST || 'smtp.hostinger.com',
    smtpPort: process.env.SMTP_PORT || '465',
    smtpUser: process.env.SMTP_USER || 'support@neibrpay.com',
    smtpPassword: process.env.SMTP_PASSWORD || '',
    supportEmail: process.env.SUPPORT_EMAIL || 'support@neibrpay.com',
  },
});
