I'll create a comprehensive markdown file with the Nuxt.js marketing site folder structure and configuration details. Since you're in ask mode, I'll provide the content for you to create the file.

Here's the markdown content for your new file:

```markdown
# NeibrPay Marketing Site - Nuxt.js Folder Structure

## Overview

This document outlines the recommended folder structure and configuration for a Nuxt.js marketing site for the NeibrPay HOA management platform, designed to integrate seamlessly with the existing monorepo architecture.

## Recommended Folder Structure
```

neibrpay/
├─ apps/
│ ├─ admin-web/ # Existing Vue 3 admin dashboard
│ ├─ owner-web/ # Existing Vue 3 owner portal
│ ├─ mobile/ # Future mobile app
│ └─ marketing/ # 🆕 NEW: Nuxt.js marketing site
│ ├─ .nuxt/ # Nuxt build output (auto-generated)
│ ├─ .output/ # Static site output (auto-generated)
│ ├─ assets/ # Static assets
│ │ ├─ css/
│ │ │ └─ main.css # Global styles
│ │ ├─ images/
│ │ │ ├─ logos/
│ │ │ ├─ screenshots/
│ │ │ ├─ icons/
│ │ │ └─ hero/
│ │ └─ fonts/
│ ├─ components/ # Vue components
│ │ ├─ ui/ # Reusable UI components
│ │ │ ├─ Button.vue
│ │ │ ├─ Card.vue
│ │ │ ├─ Modal.vue
│ │ │ └─ index.ts
│ │ ├─ sections/ # Page sections
│ │ │ ├─ Hero.vue
│ │ │ ├─ Features.vue
│ │ │ ├─ Pricing.vue
│ │ │ ├─ Testimonials.vue
│ │ │ ├─ CTA.vue
│ │ │ └─ Footer.vue
│ │ ├─ forms/ # Form components
│ │ │ ├─ ContactForm.vue
│ │ │ ├─ DemoRequest.vue
│ │ │ └─ NewsletterSignup.vue
│ │ └─ layout/ # Layout components
│ │ ├─ Header.vue
│ │ ├─ Navigation.vue
│ │ └─ MobileMenu.vue
│ ├─ composables/ # Vue composables
│ │ ├─ useAnalytics.ts
│ │ ├─ useContact.ts
│ │ └─ useSEO.ts
│ ├─ content/ # Content management
│ │ ├─ blog/
│ │ │ ├─ index.md
│ │ │ └─ posts/
│ │ ├─ case-studies/
│ │ └─ testimonials/
│ ├─ layouts/ # Page layouts
│ │ ├─ default.vue
│ │ ├─ blog.vue
│ │ └─ landing.vue
│ ├─ middleware/ # Route middleware
│ │ └─ analytics.ts
│ ├─ pages/ # File-based routing
│ │ ├─ index.vue # Homepage
│ │ ├─ about.vue
│ │ ├─ features.vue
│ │ ├─ pricing.vue
│ │ ├─ contact.vue
│ │ ├─ demo.vue
│ │ ├─ blog/
│ │ │ ├─ index.vue
│ │ │ └─ [slug].vue
│ │ ├─ case-studies/
│ │ │ ├─ index.vue
│ │ │ └─ [slug].vue
│ │ ├─ legal/
│ │ │ ├─ privacy.vue
│ │ │ ├─ terms.vue
│ │ │ └─ cookies.vue
│ │ └─ auth/
│ │ ├─ login.vue
│ │ └─ signup.vue
│ ├─ plugins/ # Nuxt plugins
│ │ ├─ analytics.client.ts
│ │ ├─ gtag.client.ts
│ │ └─ seo.client.ts
│ ├─ public/ # Static files
│ │ ├─ favicon.ico
│ │ ├─ robots.txt
│ │ ├─ sitemap.xml
│ │ ├─ images/
│ │ └─ icons/
│ ├─ server/ # Server-side code
│ │ ├─ api/
│ │ │ ├─ contact.post.ts
│ │ │ ├─ demo-request.post.ts
│ │ │ └─ newsletter.post.ts
│ │ └─ middleware/
│ │ └─ cors.ts
│ ├─ stores/ # Pinia stores
│ │ ├─ contact.ts
│ │ └─ demo.ts
│ ├─ types/ # TypeScript types
│ │ ├─ contact.ts
│ │ ├─ seo.ts
│ │ └─ global.d.ts
│ ├─ utils/ # Utility functions
│ │ ├─ seo.ts
│ │ ├─ analytics.ts
│ │ └─ validation.ts
│ ├─ .env.example
│ ├─ .gitignore
│ ├─ nuxt.config.ts
│ ├─ package.json
│ ├─ tailwind.config.js
│ └─ tsconfig.json

````

## Key Configuration Files

### `apps/marketing/package.json`
```json
{
  "name": "@neibrpay/marketing",
  "private": true,
  "scripts": {
    "build": "nuxt build",
    "dev": "nuxt dev",
    "generate": "nuxt generate",
    "preview": "nuxt preview",
    "postinstall": "nuxt prepare"
  },
  "devDependencies": {
    "@nuxt/content": "^2.8.4",
    "@nuxtjs/tailwindcss": "^6.8.4",
    "@pinia/nuxt": "^0.5.1",
    "@vueuse/nuxt": "^10.5.0",
    "nuxt": "^3.8.0",
    "typescript": "^5.2.2"
  },
  "dependencies": {
    "@headlessui/vue": "^1.7.16",
    "@heroicons/vue": "^2.0.18",
    "pinia": "^2.1.7"
  }
}
````

### `apps/marketing/nuxt.config.ts`

```typescript
export default defineNuxtConfig({
  devtools: { enabled: true },

  // Static site generation
  ssr: false,
  nitro: {
    prerender: {
      routes: ['/sitemap.xml'],
    },
  },

  // Modules
  modules: [
    '@nuxt/content',
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@vueuse/nuxt',
  ],

  // CSS
  css: ['~/assets/css/main.css'],

  // App configuration
  app: {
    head: {
      title: 'NeibrPay - HOA Management Platform',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content:
            'Streamline HOA management with NeibrPay. Automated invoicing, payment processing, and resident communication.',
        },
      ],
      link: [{ rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }],
    },
  },

  // Runtime config
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL || 'https://api.neibrpay.com',
      analyticsId: process.env.GA_ID || '',
      contactEmail: process.env.CONTACT_EMAIL || 'hello@neibrpay.com',
    },
  },

  // Content module
  content: {
    highlight: {
      theme: 'github-light',
    },
  },
});
```

## Integration with Existing Monorepo

### Update Root `package.json`

Add the marketing app to your workspaces and scripts:

```json
{
  "workspaces": ["apps/*", "packages/*"],
  "scripts": {
    "dev": "turbo run dev",
    "dev:marketing": "turbo run dev --filter=@neibrpay/marketing",
    "build": "turbo run build",
    "build:marketing": "turbo run build --filter=@neibrpay/marketing",
    "generate:marketing": "turbo run generate --filter=@neibrpay/marketing"
  }
}
```

### Update `turbo.json`

Add marketing-specific pipeline:

```json
{
  "pipeline": {
    "generate": {
      "dependsOn": ["^build"],
      "outputs": [".output/**"]
    }
  }
}
```

## Content Strategy for HOA Marketing

Based on the HOA MVP PRD, here are key pages to create:

1. **Homepage** (`pages/index.vue`) - Hero, features, social proof
2. **Features** (`pages/features.vue`) - Detailed feature breakdown
3. **Pricing** (`pages/pricing.vue`) - Transparent pricing tiers
4. **Case Studies** (`pages/case-studies/`) - Success stories
5. **Blog** (`pages/blog/`) - SEO content about HOA management
6. **Demo** (`pages/demo.vue`) - Lead generation form
7. **Contact** (`pages/contact.vue`) - Contact form and info

## SEO & Performance Features

- **Static Generation**: Pre-rendered pages for fast loading
- **Content Management**: Markdown-based blog and case studies
- **Analytics**: Google Analytics integration
- **SEO**: Meta tags, structured data, sitemap
- **Performance**: Image optimization, lazy loading
- **Accessibility**: WCAG compliance

## Development Workflow

### Initial Setup

1. Create the marketing app directory: `mkdir apps/marketing`
2. Initialize Nuxt project: `cd apps/marketing && npx nuxi@latest init .`
3. Install dependencies: `npm install`
4. Configure Tailwind CSS and other modules
5. Set up the folder structure as outlined above

### Development Commands

```bash
# Start development server
npm run dev:marketing

# Build for production
npm run build:marketing

# Generate static site
npm run generate:marketing

# Preview generated site
npm run preview:marketing
```

### Deployment

The static site can be deployed to:

- **Netlify**: Connect to Git repository, build command: `npm run generate:marketing`
- **Vercel**: Connect to Git repository, build command: `npm run generate:marketing`
- **GitHub Pages**: Use GitHub Actions to build and deploy
- **AWS S3**: Upload the `.output/public` folder

## Key Benefits

1. **Monorepo Integration**: Seamlessly fits into existing Turbo monorepo
2. **Static Generation**: Fast loading times and excellent SEO
3. **Content Management**: Easy blog and case study management
4. **Type Safety**: Full TypeScript support
5. **Modern Stack**: Vue 3, Nuxt 3, Tailwind CSS
6. **Performance**: Optimized for Core Web Vitals
7. **SEO Ready**: Built-in SEO features and meta management

This structure provides a professional marketing presence for NeibrPay while maintaining consistency with the existing codebase architecture.

```

```
