# NeibrPay - HOA Management Platform

A comprehensive cloud-based multi-tenant HOA Management Platform designed for self-managed homeowners associations in the U.S. Built with modern web technologies and a scalable architecture.

## 📖 Quick Links

**Getting Started**

- [🚀 Deployment Status](#-deployment-status) - Production deployment guides
- [🚀 Getting Started](#-getting-started-1) - Local development setup
- [📜 Available Scripts](#-available-scripts) - Development commands

**Technical Documentation**

- [🏗️ Architecture](#️-architecture) - Project structure and organization
- [🛠️ Tech Stack](#️-tech-stack) - Technologies and frameworks
- [📚 Documentation](#-documentation) - Complete documentation index
- [🌐 Deployment](#-deployment) - Production deployment architecture

**Project Status**

- [📋 Development Status](#-development-status) - Current progress and roadmap
- [🔍 Health Checks](#-health-checks) - Testing and monitoring
- [🆘 Troubleshooting](#-troubleshooting) - Common issues and solutions

## 🚀 Deployment Status

### Production Ready

- **Frontend (Admin)**: Configured for [Netlify](https://netlify.com) deployment
- **Backend (API)**: Configured for [Railway](https://railway.app) deployment
- **Deployment Guides**: See [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) for complete setup

### Quick Deploy

```bash
# 1. Deploy Backend to Railway
# - Root directory: backend/laravel
# - Set environment variables (see railway.env.example)
# - Health check: /api/health

# 2. Deploy Frontend to Netlify
# - Build command: cd apps/admin-web && npm install && npm run build
# - Publish directory: apps/admin-web/dist
# - Set VITE_API_URL to Railway backend URL

# 3. Connect services (see DEPLOYMENT_CHECKLIST.md for details)
```

📚 **Detailed Guides:**

- [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) - Complete deployment checklist
- [DEPLOYMENT_NETLIFY.md](DEPLOYMENT_NETLIFY.md) - Frontend deployment guide
- [DEPLOYMENT_RAILWAY.md](DEPLOYMENT_RAILWAY.md) - Backend deployment guide

## 🏠 Overview

NeibrPay is a complete HOA management solution that streamlines operations for self-managed communities. Built with modern technologies and designed for ease of use.

### Key Features

**For HOA Managers & Board Members**

- 💰 **Financial Management**: Invoices, payments, charges, and expense tracking
- 📊 **Dashboard & Reporting**: Real-time financial insights and analytics
- 🏘️ **Unit Management**: Track properties, residents, and ownership
- 📄 **Document Management**: Store and share HOA documents securely
- 🧾 **PDF Generation**: Professional invoices and financial reports
- 💳 **Payment Tracking**: Record and reconcile all transactions

**For Property Owners & Residents**

- 💳 **Online Payments**: Pay dues and fees online (Stripe integration planned)
- 📧 **Notifications**: Automated email notifications for important updates
- 📄 **Document Access**: View HOA documents and governing rules
- 💵 **Payment History**: Track all payments and outstanding balances
- 🏠 **Unit Information**: Access property-specific details

**Technical Features**

- 🔒 **Multi-tenant Architecture**: Secure, isolated data for each HOA
- 🔐 **Firebase Authentication**: Secure email/password authentication
- 📱 **Responsive Design**: Works on desktop, tablet, and mobile
- ⚡ **Modern Tech Stack**: Vue 3, Laravel 11, TypeScript
- 🚀 **Cloud-Ready**: Deployable to Netlify and Railway
- 📊 **Type-Safe**: Full TypeScript coverage for reliability

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
- **jsPDF** for client-side PDF generation
- **html2canvas** for PDF rendering
- **Firebase SDK** for authentication

### Backend

- **Laravel 11** with Eloquent ORM
- **Laravel Sanctum** for API authentication
- **SQLite** for development (MySQL/PostgreSQL for production)
- **Laravel DomPDF** for server-side PDF generation
- **Firebase PHP SDK** for authentication
- **Multi-tenant architecture** with tenant scoping

### Development Tools

- **Turbo** for monorepo build orchestration
- **npm workspaces** for dependency management
- **TypeScript** for type safety across all packages
- **ESLint** + **Prettier** for code quality
- **Husky** for Git hooks
- **Vitest** for testing

### Cloud Services & Integrations

- **Netlify** for frontend hosting
- **Railway** for backend hosting
- **Firebase Auth** for authentication (integrated)
- **n8n** for workflow automation (optional)
- **Stripe** for payment processing (planned)
- **Plaid** for bank integrations (planned)
- **AWS S3** for file storage (planned)
- **SendGrid/SES** for email notifications (planned)

## 🚀 Getting Started

### Prerequisites

- **Node.js** >= 18.0.0
- **npm** >= 8.0.0
- **PHP** >= 8.2
- **Composer**
- **SQLite** (default) or **MySQL** >= 8.0

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

### Plaid Bank Integration - Running Sync Commands

#### Local Development

**Manual Sync (One-time)**

Run the Plaid transaction sync command manually in your terminal:

```bash
cd backend/laravel
php artisan plaid:sync
```

This will:

1. Fetch all active Plaid bank accounts from the database
2. Sync transactions for each account via the Plaid API
3. Log results and any errors
4. Update account status if errors occur

**Monitor Queue Jobs (if using queue)**

If you're using the queue driver:

```bash
cd backend/laravel

# Start the queue worker in a separate terminal
php artisan queue:work

# In another terminal, dispatch sync jobs
php artisan plaid:sync
```

**View Logs**

Check sync operation results:

```bash
cd backend/laravel
tail -f storage/logs/laravel.log | grep -i plaid
```

#### Production Deployment (Railway.app)

The Plaid sync is configured to run automatically on a **schedule every hour** using Laravel's task scheduler.

**Setup Steps:**

1. **Set Plaid Credentials on Railway**

   In your Railway environment variables, add:

   ```env
   PLAID_CLIENT_ID=your_plaid_client_id
   PLAID_SECRET=your_plaid_secret
   PLAID_ENV=sandbox  # or production
   ```

2. **Enable Scheduler on Railway**

   Create a **Cron Job** process in Railway that runs Laravel's scheduler:

   ```bash
   cd /app && php artisan schedule:run >> /dev/null 2>&1
   ```

   **Configuration Steps:**
   - Go to your Railway project dashboard
   - Add a new service: **Cron Job**
   - Set command: `php /app/artisan schedule:run >> /dev/null 2>&1`
   - Set frequency: **Every hour** (or your preferred interval)
   - Set working directory: `/app` (Railway root)

3. **Alternative: Use an External Scheduler**

   If Railway's cron is unreliable, use an external service:
   - **EasyCron** (free): https://www.easycron.com/
   - **Cron-job.org** (free): https://cron-job.org/

   Set webhook to your Railway API:

   ```
   https://your-railway-domain.railway.app/api/schedule/plaid
   ```

4. **Monitor Sync on Railway**

   View logs in Railway dashboard:
   - Go to Logs tab
   - Filter by keyword: `plaid` or `Plaid sync job`
   - Look for success/error messages

5. **Manual Trigger on Railway**

   Trigger sync manually via API (if endpoint available):

   ```bash
   curl -X POST https://your-railway-domain.railway.app/api/plaid/sync \
     -H "Authorization: Bearer YOUR_API_TOKEN" \
     -H "Content-Type: application/json"
   ```

#### Scheduler Configuration Details

The scheduler is configured in `backend/laravel/app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Sync Plaid transactions every hour
    $schedule->command('plaid:sync')
        ->hourly()
        ->withoutOverlapping()  // Prevent overlapping executions
        ->onFailure(function () {
            Log::error('Plaid sync job failed');
        })
        ->onSuccess(function () {
            Log::info('Plaid sync job completed successfully');
        });
}
```

**Key Features:**

- ✅ Runs every hour
- ✅ Prevents overlapping executions (only 1 sync at a time)
- ✅ Logs success and failure events
- ✅ Auto-updates account status on errors

#### Troubleshooting

**Sync Not Running?**

1. Verify Plaid credentials in `.env`:

   ```bash
   grep PLAID backend/laravel/.env
   ```

2. Check that active bank accounts exist in database:

   ```bash
   php artisan tinker
   >>> App\Models\PlaidBankAccount::where('status', 'active')->count()
   ```

3. Check logs for errors:

   ```bash
   tail -f backend/laravel/storage/logs/laravel.log | grep -i plaid
   ```

4. On Railway, verify cron job is running:
   - Check Railway dashboard → Deployments → Job logs
   - Look for `php artisan schedule:run` executions

**Common Issues:**

| Issue                           | Solution                                            |
| ------------------------------- | --------------------------------------------------- |
| "No active Plaid bank accounts" | Ensure accounts are created and status is 'active'  |
| Authentication errors           | Verify PLAID_CLIENT_ID and PLAID_SECRET are correct |
| Sync hangs or times out         | Check queue connection settings or increase timeout |
| Railway cron not triggering     | Enable cron job service or use external scheduler   |

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

**Current Phase**: Core MVP Features Complete - Ready for Deployment

### ✅ Completed

**Infrastructure & Setup**

- ✅ Monorepo structure with Turbo build system
- ✅ Vue 3 + Laravel 11 architecture
- ✅ TypeScript strict mode across all packages
- ✅ Deployment configurations (Netlify + Railway)
- ✅ CORS and authentication setup

**Authentication & Multi-tenancy**

- ✅ Firebase authentication integration (email/password)
- ✅ Multi-tenant database architecture
- ✅ Tenant-scoped data isolation
- ✅ User roles and permissions (Manager, Owner)

**Core HOA Management Features**

- ✅ Invoice management with PDF generation (client + server)
- ✅ Payment tracking and entry system
- ✅ Charge management (one-time and recurring)
- ✅ Unit and resident management
- ✅ Expense and vendor tracking
- ✅ Document management with file upload

**UI/UX**

- ✅ Modern Bonsai-inspired design system
- ✅ Responsive layout (desktop, tablet, mobile)
- ✅ Dark mode support
- ✅ Accessible components (WCAG AA)
- ✅ Loading states and error handling

### 🔄 In Progress

- Advanced financial reporting dashboards
- Email notifications via n8n workflows
- Performance optimization and caching
- Comprehensive testing suite

### 📅 Planned

**Phase 2 - Payments**

- Stripe payment processing integration
- ACH/eCheck payments
- Payment plans and installments
- Late fee automation

**Phase 3 - Advanced Features**

- Bank reconciliation
- Budget planning and forecasting
- Advanced analytics and insights
- Document signing (DocuSign integration)

**Phase 4 - Mobile & Integrations**

- Native mobile app (Capacitor/Ionic)
- Plaid bank account linking
- QuickBooks integration
- SMS notifications

See [TASKS.md](./md/TASKS.md) for detailed development roadmap.

## 📚 Documentation

### Deployment Guides

- **[Deployment Checklist](./DEPLOYMENT_CHECKLIST.md)** - Complete deployment walkthrough
- **[Netlify Deployment](./DEPLOYMENT_NETLIFY.md)** - Frontend deployment guide
- **[Railway Deployment](./DEPLOYMENT_RAILWAY.md)** - Backend deployment guide

### API & Technical Documentation

- **[API Documentation](./md/INVOICE_API_DOCUMENTATION.md)** - Complete API reference
- **[PDF Generation Guide](./md/INVOICE_PDF_GENERATION.md)** - PDF implementation details
- **[Schema Documentation](./md/NeibrPay_Schema_PRD_MVP.md)** - Database schema and relationships

### Product & Design Documentation

- **[HOA MVP PRD](./md/HOA_MVP_PRD.md)** - Product requirements document
- **[UI/UX Guide](./md/NeibrPay_UIUX_PRD.md)** - Design system and user experience
- **[Manager Dashboard PRD](./md/NeibrPay_HOA_Manager_Dashboard_PRD.md)** - Dashboard specifications
- **[Owner Portal PRD](./md/NeibrPay_Owner_Portal_PRD.md)** - Owner portal specifications

### Development Documentation

- **[Development Tasks](./md/TASKS.md)** - Development roadmap and tasks
- **[Codebase Improvements](./md/CODEBASE_IMPROVEMENTS.md)** - Technical debt and improvements
- **[n8n Workflow Guides](./docs/)** - Email notification workflow setup

## 🌐 Deployment

### Deployment Architecture

**Frontend (Netlify)**

- Platform: Netlify
- Build: `cd apps/admin-web && npm install && npm run build`
- Output: `apps/admin-web/dist`
- Features: Automatic SSL, CDN, Preview deployments
- Configuration: `apps/admin-web/netlify.toml`

**Backend (Railway)**

- Platform: Railway
- Root: `backend/laravel`
- Runtime: PHP 8.2 with Laravel 11
- Database: SQLite (dev), PostgreSQL (production recommended)
- Health Check: `/api/health`
- Configuration: `backend/laravel/railway.toml`

**Key Environment Variables**

Frontend (Netlify):

```env
VITE_API_URL=https://your-railway-domain.railway.app/api
```

Backend (Railway):

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://your-railway-domain.railway.app
FRONTEND_URL=https://your-netlify-domain.netlify.app
DB_CONNECTION=sqlite

# Plaid Configuration
PLAID_CLIENT_ID=your_plaid_client_id
PLAID_SECRET=your_plaid_secret
PLAID_ENV=sandbox  # or production
```

### Deployment Steps

1. **Deploy Backend First**

   ```bash
   # See DEPLOYMENT_RAILWAY.md for details
   # Set environment variables on Railway
   # Verify health endpoint: /api/health
   ```

2. **Deploy Frontend**

   ```bash
   # See DEPLOYMENT_NETLIFY.md for details
   # Set VITE_API_URL to Railway backend URL
   # Verify CORS configuration
   ```

3. **Connect Services**

   ```bash
   # Update FRONTEND_URL on Railway
   # Test authentication flow
   # Verify API connectivity
   ```

4. **Setup Plaid Integration (Optional)**

   ```bash
   # Set Plaid credentials in Railway environment:
   # - PLAID_CLIENT_ID
   # - PLAID_SECRET
   # - PLAID_ENV (sandbox or production)

   # Enable task scheduler for automatic sync:
   # Add a Cron Job service in Railway
   # Command: php /app/artisan schedule:run >> /dev/null 2>&1
   # Frequency: Every hour (or as needed)

   # See "Plaid Bank Integration - Running Sync Commands" section above
   ```

📖 **Complete Guide**: See [DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md)

### Future Infrastructure

- **Database**: PostgreSQL with automated backups
- **Storage**: AWS S3 for document and PDF storage
- **CDN**: CloudFront for asset delivery
- **Email**: SendGrid or AWS SES for notifications
- **Monitoring**: Sentry for error tracking
- **Analytics**: PostHog or Mixpanel for product analytics

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

## 🔍 Health Checks

**Local Development:**

```bash
# Backend health check
curl http://localhost:8000/api/health

# Expected response:
# {"status":"ok","timestamp":"2024-11-10T12:00:00.000000Z"}
```

**Production:**

```bash
# Backend health check
curl https://your-railway-domain.railway.app/api/health

# Frontend health check
# Open browser: https://your-netlify-domain.netlify.app
```

## 🆘 Troubleshooting

### Common Issues

**Build Errors**

- Ensure all packages are installed: `npm install`
- Clear cache: `npm run clean && npm install`
- Check Node.js version: `node --version` (requires >= 18.0.0)

**CORS Errors**

- Verify `FRONTEND_URL` is set on Railway
- Check `backend/laravel/config/cors.php` configuration
- Ensure cookies are sent with credentials

**401 Unauthorized**

- Verify Laravel `APP_KEY` is set
- Check Firebase authentication configuration
- Ensure Sanctum is configured correctly

**Database Issues**

- Check SQLite file permissions
- Run migrations: `php artisan migrate`
- Verify database path in `.env`

**PDF Generation Issues**

- Check DomPDF configuration
- Verify storage directory permissions
- Ensure fonts are accessible

See [DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md) for more troubleshooting tips.

## 📄 License

Private - All rights reserved

---

**NeibrPay** - Streamlining HOA management for modern communities.

For support, questions, or contributions, please refer to the documentation in the `/md` directory or open an issue.

**\* Stripe Test account \*\***

stripe listen --forward-to localhost:8000/api/stripe/webhook

CARDS
4000 0000 0000 0002 - Card declined
4000 0000 0000 9995 - Insufficient funds
4000 0025 0000 3155 - 3D Secure authentication required

Visa: 4242 4242 4242 4242
Mastercard: 5555 5555 5555 4444
American Express: 3782 822463 10005
Discover: 6011 1111 1111 1117

ACH Payments
Test Account Number: 000123456789
Test Routing Number: 110000000
Test Account Holder: Any name

---

**N8N MCP**
npx n8n-mcp

**PLAID Sandbox**
Username: user_good
Password: pass_good
Phone: 1111111111
