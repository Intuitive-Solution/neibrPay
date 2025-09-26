# Product Requirements Document (PRD) - MVP (Phase 1)

## 1. Executive Summary
We are building a cloud-based multi-tenant HOA Management Platform for self-managed associations in the U.S.  

The MVP will deliver core financial management, communication, document storage, vendor/expense management, and owner portals to prove market fit.  

**Target users:** HOA board members, homeowners, and bookkeepers.

---

## 2. Objectives
1. Enable HOAs to invoice and collect dues online.  
2. Provide transparent owner access to payments and community documents.  
3. Support basic communication between boards and residents (email + push notifications).  
4. Manage vendor relationships and track HOA expenses.  
5. Ensure multi-tenant hosting so multiple HOAs can run on the same cloud platform.  

---

## 3. MVP Features

### 3.1 Financial / Accounting
- Invoicing & Payments (Stripe, ACH, Card), late fees, PDF download & view in portal.  
- General Ledger Accounting with income/expense tracking.  
- Budgets & Reports: annual budget, budget vs. actual, export reports.  
- Bank Integrations: Plaid + CSV fallback, reconciliation.  
- **Vendor Management**:  
  - Add/manage vendors with contact info, tax IDs, and payment terms.  
  - Vendor directory with active/inactive status.  
- **Expense Management**:  
  - Record vendor bills (draft → approved → paid).  
  - Attach PDF/image of invoices to bills.  
  - Expense line items linked to GL accounts.  
  - Vendor payments (ACH, check, card, wire).  
  - A/P Aging reports.  

### 3.2 Communication
- Email notifications (invoices, confirmations, announcements) **via Hostinger Mail SMTP**.  
- Push notifications (Firebase FCM).  

### 3.3 Management & Operations
- Document storage on AWS S3, tenant/year/category organization.  
- Owner portals with dashboard for dues, payments, announcements, documents, invoices in PDF.  

---

## 4. Compliance Requirements
- PCI compliance (via Stripe).  
- 7-year financial record retention.  
- Role-based access control.  
- GDPR/CCPA compliance.  

---

## 5. Tech Stack & Planned Libraries

### Frontend (Vue 3)
- Vue 3 + Vite, Shadcn/Vue, TanStack Query, Pinia, Vue Router, Axios  
- PDF viewing: pdf.js (vue-pdf/pdfjs-dist)  
- Firebase SDK (auth + push)  

### Backend (Laravel)
- Laravel 11 + Eloquent  
- Firebase Admin SDK  
- Stripe SDK, Plaid SDK (or Guzzle)  
- PDF generation: Dompdf or Laravel Snappy  
- AWS S3 via Laravel Filesystem  
- **Laravel Mail with Hostinger SMTP**  
- OpenAPI/Swagger  

### Database
- MySQL: users, tenants, memberships, invoices, payments, expenses, vendors, documents, announcements.  

## Repo Layout
neibrpay/
├─ apps/
│  ├─ admin-web/          # Vue 3 (manager dashboard)
│  ├─ owner-web/          # Vue 3 (owner portal)
│  └─ mobile/             # (future) Capacitor/Ionic or RN/Flutter; can start as placeholder
├─ packages/
│  ├─ ui/                 # shared Vue components, theming, tokens (Shadcn/Vue + Tailwind CSS)
│  ├─ api-client/         # typed SDK: axios wrapper, endpoints, interceptors, DTOs
│  ├─ models/             # zod/yup schemas, TS types, currency & date helpers
│  └─ config/             # eslint, prettier, tsconfig, commitlint, Git hooks
├─ backend/
│  └─ laravel/            # Laravel API, migrations, seeders, OpenAPI spec
├─ infra/
│  ├─ docker/             # local dev containers (db, redis, mailhog if needed)
│  └─ deploy/             # IaC or CI deployment scripts
└─ .github/workflows/     # CI pipelines for apps and backend


### Infrastructure
- Railway.app(backend) , Netlify (frontend)  
- GitHub Actions CI/CD, Sentry, CloudWatch  

---

## 6. Architecture
- Multi-tenant SaaS (row-level MySQL tenant IDs).  
- Auth: Firebase for identity, Laravel for verification & mapping to MySQL users.  
- API-first backend with Laravel REST.  

---

## 7. User Roles
- Board Admins: invoices, budgets, vendors, expenses, docs, announcements.  
- Residents: payments, history, docs, invoices.  
- Bookkeepers: accounting-only.  
- Super Admin: tenant onboarding & support.  

---

## 8. Security
- Enforce email_verified = true.  
- MFA for board admins.  
- RBAC in MySQL, tenant isolation on all queries.  
- Audit logs for sensitive actions.  

---

## 9. Non-Functional Requirements
- API <200ms response.  
- 99.9% uptime SLA.  
- WCAG 2.1 accessibility.  
- TLS 1.2+, RBAC.  
- Scale: 50+ HOAs, 10k+ users.  

---

## 10. MVP Roadmap
- Firebase Auth + tenant handling  
- Multi-tenant setup  
- Financials: invoicing, payments, GL, budgets, reports, Plaid  
- Vendor & Expense management  
- Email (Hostinger SMTP) + Push notifications  
- Document storage  
- Owner portal with PDF invoice viewing  

**Deferred (Phase 2+):**
- OCR/AI ingestion  
- WhatsApp/SMS/Voice  
- Violations  
- Voting & Surveys  
