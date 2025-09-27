# NeibrPay - HOA Management Platform

A comprehensive cloud-based multi-tenant HOA Management Platform designed for self-managed homeowners associations in the U.S. Built with modern web technologies and a scalable architecture.

## 🏠 Overview

NeibrPay streamlines HOA operations by providing:

- **Manager Dashboard**: Financial management, member administration, reporting
- **Owner Portal**: Dues payment, document access, community announcements
- **Multi-tenant Architecture**: Secure, isolated data for each HOA
- **Modern UI/UX**: Responsive design with intuitive workflows

## 🏗️ Architecture

This is a **monorepo** built with modern development practices:

```
neibrpay/
├── apps/                    # Frontend Applications
│   ├── admin-web/          # Vue 3 HOA Manager Dashboard
│   ├── owner-web/          # Vue 3 Property Owner Portal
│   └── mobile/             # Future: Mobile App (Capacitor/Ionic)
├── packages/               # Shared Libraries
│   ├── ui/                 # Shared Vue components & design system
│   ├── api-client/         # Typed API SDK with axios wrapper
│   ├── models/             # Data schemas, types & utilities
│   └── config/             # Shared configuration files
├── backend/                # Backend Services
│   └── laravel/            # Laravel 11 API with multi-tenant support
├── infra/                  # Infrastructure & Deployment
│   └── deploy/             # CI/CD scripts & IaC
└── md/                     # Documentation & Requirements
    ├── TASKS.md            # Development roadmap
    └── *.md                # Product requirements & specifications
```

## 🛠️ Tech Stack

### Frontend

- **Vue 3** + Composition API + TypeScript
- **Vite** for fast development and building
- **Tailwind CSS** for utility-first styling
- **Pinia** for state management
- **Vue Router** for client-side routing
- **TanStack Query** for server state management
- **Axios** for HTTP requests
- **PDF.js** for document viewing

### Backend

- **Laravel 11** with Eloquent ORM
- **Laravel Sanctum** for API authentication
- **MySQL** for primary database
- **Multi-tenant architecture** with tenant scoping

### Development Tools

- **Turbo** for monorepo build orchestration
- **npm workspaces** for dependency management
- **TypeScript** for type safety
- **ESLint** + **Prettier** for code quality
- **Husky** for Git hooks
- **Vitest** for testing

### Planned Integrations

- **Firebase Auth** for authentication
- **Stripe** for payment processing
- **Plaid** for bank integrations
- **AWS S3** for file storage
- **SendGrid/SES** for email notifications

## 🚀 Getting Started

### Prerequisites

- **Node.js** >= 18.0.0
- **npm** >= 8.0.0
- **PHP** >= 8.1
- **Composer**
- **MySQL** >= 8.0

### Installation

1. **Clone the repository:**

   ```bash
   git clone <repository-url>
   cd neibrpay
   ```

2. **Install dependencies:**

   ```bash
   npm install
   ```

3. **Set up Laravel backend:**

   ```bash
   cd backend/laravel
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   cd ../..
   ```

4. **Start development servers:**

   ```bash
   npm run dev
   ```

   This will start:
   - Admin Web App: `http://localhost:3000`
   - Owner Web App: `http://localhost:3001`
   - Laravel API: `http://localhost:8000`

## 📜 Available Scripts

| Script               | Description                           |
| -------------------- | ------------------------------------- |
| `npm run dev`        | Start all development servers         |
| `npm run build`      | Build all applications for production |
| `npm run test`       | Run all tests                         |
| `npm run lint`       | Lint all code with ESLint             |
| `npm run type-check` | Type check all TypeScript code        |
| `npm run clean`      | Clean all build artifacts             |
| `npm run format`     | Format all code with Prettier         |

### Individual App Scripts

Each app supports individual commands:

```bash
# Admin Web
cd apps/admin-web
npm run dev
npm run build
npm run test

# Owner Web
cd apps/owner-web
npm run dev
npm run build
npm run test

# Laravel Backend
cd backend/laravel
php artisan serve
php artisan test
```

## 🏛️ Project Structure Details

### Frontend Applications

#### Admin Web (`apps/admin-web/`)

- **Target Users**: HOA Board Members, Property Managers
- **Key Features**: Financial dashboard, member management, reporting
- **Theme**: Blue color scheme (`bg-blue-600`)

#### Owner Web (`apps/owner-web/`)

- **Target Users**: Property Owners, Residents
- **Key Features**: Dues payment, document access, announcements
- **Theme**: Green color scheme (`bg-green-600`)

### Shared Packages

#### UI Package (`@neibrpay/ui`)

- Shared Vue components and design system
- Tailwind CSS configuration and utilities
- Reusable composables and hooks

#### API Client (`@neibrpay/api-client`)

- Typed HTTP client with Axios
- API endpoint definitions
- Request/response interceptors
- Error handling and retry logic

#### Models (`@neibrpay/models`)

- Zod schemas for data validation
- TypeScript type definitions
- Currency and date utility functions
- Business logic helpers

#### Config (`@neibrpay/config`)

- Shared ESLint and Prettier configurations
- TypeScript compiler options
- Git hooks and commit linting rules

## 🔧 Development Workflow

### Monorepo Management

- **Turbo** handles build orchestration and caching
- **npm workspaces** manages dependencies across packages
- **Path mapping** enables clean imports: `@neibrpay/ui`, `@neibrpay/api-client`

### Code Quality

- **Pre-commit hooks** run linting and formatting
- **Conventional commits** for consistent commit messages
- **TypeScript strict mode** for type safety
- **ESLint** enforces coding standards

### Testing Strategy

- **Unit tests** with Vitest for Vue components
- **Integration tests** for API endpoints
- **End-to-end tests** planned with Playwright

## 📋 Development Status

**Current Phase**: Project Setup (Phase 0)

### ✅ Completed

- Monorepo structure with Turbo
- Vue 3 + Laravel boilerplate
- Build system configuration
- Basic routing and components
- TypeScript setup across all packages

### 🔄 In Progress

- Package implementations (UI, API client, models)
- Database schema design
- Authentication system
- CI/CD pipeline setup

### 📅 Planned

- Multi-tenant database architecture
- Firebase authentication integration
- Payment processing with Stripe
- Document management system
- Email notifications
- Mobile application

See [TASKS.md](./md/TASKS.md) for detailed development roadmap.

## 🌐 Deployment

### Planned Infrastructure

- **Frontend**: Netlify for static hosting
- **Backend**: Railway.app for Laravel API
- **Database**: MySQL with automated backups
- **Storage**: AWS S3 for document storage
- **CDN**: CloudFront for asset delivery
- **Monitoring**: Sentry for error tracking

### Environment Setup

```bash
# Production environment variables
FRONTEND_URL=https://app.neibrpay.com
API_URL=https://api.neibrpay.com
DATABASE_URL=mysql://...
STRIPE_SECRET_KEY=sk_live_...
FIREBASE_PROJECT_ID=...
```

## 🤝 Contributing

1. **Fork the repository** and create a feature branch
2. **Follow the coding standards** (ESLint, Prettier, TypeScript)
3. **Write tests** for new functionality
4. **Update documentation** as needed
5. **Submit a pull request** with a clear description

### Development Guidelines

- Use **conventional commits** for commit messages
- Follow **Vue 3 Composition API** patterns
- Implement **responsive design** with Tailwind CSS
- Write **type-safe** code with TypeScript
- Include **error handling** and loading states

## 📄 License

Private - All rights reserved

---

**NeibrPay** - Streamlining HOA management for modern communities.
