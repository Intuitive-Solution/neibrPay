# NeibrPay - HOA Management Platform

A cloud-based multi-tenant HOA Management Platform for self-managed associations in the U.S.

## Project Structure

This is a monorepo containing multiple applications and shared packages:

```
neibrpay/
├── apps/
│   ├── admin-web/          # Vue 3 (manager dashboard)
│   ├── owner-web/          # Vue 3 (owner portal)
│   └── mobile/             # (future) Capacitor/Ionic or RN/Flutter
├── packages/
│   ├── ui/                 # shared Vue components, theming, tokens (Shadcn/Vue + Tailwind CSS)
│   ├── api-client/         # typed SDK: axios wrapper, endpoints, interceptors, DTOs
│   ├── models/             # zod/yup schemas, TS types, currency & date helpers
│   └── config/             # eslint, prettier, tsconfig, commitlint, Git hooks
├── backend/
│   └── laravel/            # Laravel API, migrations, seeders, OpenAPI spec
├── infra/
│   └── deploy/             # IaC or CI deployment scripts
└── .github/workflows/      # CI pipelines for apps and backend
```

## Tech Stack

### Frontend

- **Vue 3** + Vite
- **Shadcn/Vue** + Tailwind CSS
- **TanStack Query** for data fetching
- **Pinia** for state management
- **Vue Router** for routing
- **Axios** for HTTP requests
- **pdf.js** for PDF viewing

### Backend

- **Laravel 11** + Eloquent
- **Firebase Admin SDK** for authentication
- **Stripe SDK** for payments
- **Plaid SDK** for bank integrations
- **AWS S3** for file storage
- **MySQL** for database

### Infrastructure

- **Railway.app** for backend hosting
- **Netlify** for frontend hosting
- **GitHub Actions** for CI/CD
- **Sentry** for error monitoring

## Getting Started

### Prerequisites

- Node.js >= 18.0.0
- npm >= 8.0.0
- PHP >= 8.1
- Composer
- MySQL

### Installation

1. Clone the repository:

```bash
git clone <repository-url>
cd neibrpay
```

2. Install dependencies:

```bash
npm install
```

3. Set up environment variables:

```bash
cp .env.example .env
# Edit .env with your configuration
```

4. Start development servers:

```bash
npm run dev
```

## Available Scripts

- `npm run dev` - Start all development servers
- `npm run build` - Build all applications
- `npm run test` - Run all tests
- `npm run lint` - Lint all code
- `npm run type-check` - Type check all TypeScript code
- `npm run clean` - Clean all build artifacts
- `npm run format` - Format all code with Prettier

## Development Workflow

This monorepo uses:

- **Turbo** for build orchestration and caching
- **npm workspaces** for dependency management
- **Husky** for Git hooks
- **Prettier** for code formatting
- **ESLint** for code linting

## Contributing

1. Create a feature branch from `main`
2. Make your changes
3. Run tests and linting: `npm run test && npm run lint`
4. Commit your changes with conventional commits
5. Push and create a pull request

## License

Private - All rights reserved
