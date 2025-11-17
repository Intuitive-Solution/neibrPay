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
      title: 'NeibrPay - HOA Management Made Simple',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content:
            'Comprehensive HOA management platform for invoicing, payments, vendor management, and community communication.',
        },
      ],
      link: [{ rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' }],
    },
  },
  runtimeConfig: {
    public: {
      adminWebUrl: process.env.ADMIN_WEB_URL || 'http://localhost:3000',
      calendlyUrl:
        process.env.CALENDLY_URL ||
        'https://calendly.com/imailtahir/neibrpay-demo',
      posthogKey: process.env.POSTHOG_KEY || '',
      posthogHost: process.env.POSTHOG_HOST || 'https://us.i.posthog.com',
    },
    // Server-side only (not exposed to client)
    smtpHost: process.env.SMTP_HOST || 'smtp.hostinger.com',
    smtpPort: process.env.SMTP_PORT || '465',
    smtpUser: process.env.SMTP_USER || 'support@neibrpay.com',
    smtpPassword: process.env.SMTP_PASSWORD || '',
    supportEmail: process.env.SUPPORT_EMAIL || 'support@neibrpay.com',
  },
});
