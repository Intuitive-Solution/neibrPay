// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  devServer: {
    port: 3002,
  },
  modules: ['@nuxtjs/tailwindcss', '@nuxtjs/google-fonts'],
  /** Single entry: default `assets/css/tailwind.css` is missing, so the module would inject Tailwind’s built-in CSS *and* this file was listed in `css[]`, duplicating layers. */
  tailwindcss: {
    cssPath: '~/assets/css/main.css',
  },
  googleFonts: {
    families: {
      Inter: [400, 500, 600, 700],
    },
    display: 'swap',
    preload: true,
  },
  app: {
    head: {
      title:
        'NeibrPay HOA Dues Collection & Community Management Platform for Self Managed Boards',
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
      // Google Analytics is injected at runtime in `plugins/gtag.client.ts` so localhost / 127.0.0.1 are excluded.
    },
  },
  nitro: {
    // Static export (`nuxt generate`) cannot deploy server routes — `/api/contact` would 404.
    // On Netlify, use the netlify preset + `nuxt build` so API handlers become serverless functions.
    // Local `nuxt generate` / non-Netlify builds keep `static` (avoids netlify output dirs during generate).
    preset: process.env.NETLIFY ? 'netlify' : 'static',
    prerender: {
      routes: [
        '/',
        '/sitemap.xml',
        '/about',
        '/contact',
        '/support',
        '/privacy',
        '/terms',
        '/get-started',
        '/blog',
        '/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026',
        '/blog/florida-hoa-management-software-self-managed-boards-2026',
        '/blog/california-hoa-management-software-self-managed-boards-2026',
        '/blog/texas-hoa-management-software-self-managed-boards-2026',
        '/blog/arizona-hoa-management-software-self-managed-boards-2026',
        '/blog/colorado-hoa-management-software-self-managed-boards-2026',
        '/blog/nevada-hoa-management-software-self-managed-boards-2026',
        '/blog/georgia-hoa-management-software-self-managed-boards-2026',
        '/blog/north-carolina-hoa-management-software-self-managed-boards-2026',
        '/blog/washington-hoa-management-software-self-managed-boards-2026',
        '/blog/illinois-hoa-management-software-self-managed-boards-2026',
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
    },
    // Server-side only (not exposed to client)
    smtpHost: process.env.SMTP_HOST || 'smtp.hostinger.com',
    smtpPort: process.env.SMTP_PORT || '465',
    smtpUser: process.env.SMTP_USER || 'support@neibrpay.com',
    smtpPassword: process.env.SMTP_PASSWORD || '',
    supportEmail: process.env.SUPPORT_EMAIL || 'support@neibrpay.com',
  },
});
