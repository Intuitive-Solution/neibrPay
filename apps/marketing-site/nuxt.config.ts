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
      adminWebUrl: process.env.ADMIN_WEB_URL || 'http://localhost:5173',
      calendlyUrl:
        process.env.CALENDLY_URL ||
        'https://calendly.com/imailtahir/neibrpay-demo',
    },
  },
});
