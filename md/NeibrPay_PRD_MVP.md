# NeibrPay - Product Requirements Document (PRD) – MVP (Phase 1)

## 1. Executive Summary
We are building **NeibrPay**, a cloud-based multi-tenant HOA Management Platform for self-managed associations in the U.S.  
The MVP will deliver **core financial management, communication, document storage, and owner portals** to prove market fit.  
Target users: **HOA board members, homeowners, and bookkeepers**.  

---

## 2. Objectives
1. Enable HOAs to **invoice and collect dues online**.  
2. Provide **transparent owner access** to payments and community documents.  
3. Support **basic communication** between boards and residents (email + push notifications).  
4. Ensure **multi-tenant hosting** so multiple HOAs can run on the same cloud platform.  

---

## 3. MVP Features

### 3.1 Financial / Accounting
- **Invoicing & Payments**
  - Generate invoices for dues, fines, late fees.  
  - Support online payments via Stripe (ACH, card).  
  - Auto-calculate and apply late fees.  
  - Payment history visible in owner portal.  
  - Invoices downloadable and viewable as **PDF**.  

- **General Ledger Accounting**
  - Record all income and expenses (cash & accrual).  
  - Categorize transactions.  
  - Export basic financial statements (Balance Sheet, Income Statement).  

- **Budgets & Reports**
  - Allow board members to set annual budget.  
  - Compare actual vs. budget.  
  - Board-level reporting dashboard.  

- **Bank Integrations**
  - Import bank transactions via Plaid.  
  - Manual CSV upload fallback.  
  - Simple reconciliation tool.  

---

### 3.2 Communication
- **Email Notifications**
  - Invoice reminders, payment confirmations, board announcements.  

- **Push Notifications (Firebase)**
  - Alerts for invoices, payments, announcements.  
  - Configurable per-user notification settings.  

---

### 3.3 Management & Operations
- **Document Storage**
  - Centralized **AWS S3** storage.  
  - Organized by HOA → Year → Category (Bylaws, Minutes, Notices).  
  - Role-based permissions (board vs owners).  

- **Owner Portals**
  - Secure login with **Firebase Auth**.  
  - Dashboard showing dues, payments, announcements.  
  - Access to community documents.  
  - Inline PDF invoice viewing.  

---

## 4. Compliance Requirements
- PCI compliance (via Stripe).  
- Financial record retention (7 years).  
- Role-based access control (RBAC).  
- GDPR/CCPA compliance.  

---

## 5. Tech Stack & Planned Libraries

### Frontend (Vue 3)
- **Framework:** Vue 3 + Vite  
- **UI:** Vuetify  
- **State Management:** Pinia  
- **Routing:** Vue Router  
- **HTTP:** Axios  
- **PDF Viewing:** `pdf.js` (`vue-pdf` or `pdfjs-dist`)  
- **Auth SDK:** Firebase Web SDK  
- **Push:** Firebase Cloud Messaging  

### Backend (Laravel PHP 8+)
- **Framework:** Laravel 11  
- **ORM:** Eloquent  
- **Auth:** Firebase Admin SDK (PHP)  
- **Payments:** Stripe PHP SDK  
- **Bank Integration:** Plaid PHP SDK (or Guzzle REST)  
- **PDF Generation:** Dompdf or Laravel Snappy (wkhtmltopdf)  
- **File Storage:** AWS S3 via Laravel Filesystem  
- **Email:** Laravel Mail (SES/SendGrid)  
- **API Docs:** Laravel OpenAPI/Swagger  

### Database
- **Supabase (Postgres)**  
- Schema includes: `users`, `tenants`, `memberships`, `invoices`, `payments`, `documents`, `announcements`.  

### Infrastructure
- **Hosting:** AWS (EC2/ECS), Netlify (frontend)  
- **CI/CD:** GitHub Actions  
- **Monitoring:** Sentry + CloudWatch  

---

## 6. Architecture
- **Multi-tenant SaaS** with tenant IDs in Postgres (row-level access).  
- **Auth Flow:** Firebase Auth → Laravel verifies ID token → maps to Postgres `users`.  
- **Authorization:** Tenant membership & roles in Postgres (`memberships` table).  
- **API-first backend** using Laravel REST.  

---

## 7. User Roles
- **Board Admins** – Manage invoices, budgets, documents, announcements.  
- **Residents / Owners** – Pay dues, view history, access documents, invoices.  
- **Bookkeepers** – Accounting-only access.  
- **Super Admin (SaaS Owner)** – Tenant onboarding & support.  

---

## 8. Security
- Enforce **email_verified = true**.  
- Require **MFA (Firebase)** for board admins.  
- Store **roles & memberships** in Postgres.  
- Tenant isolation via `tenant_id` scoping.  
- Audit logs for sensitive actions.  

---

## 9. Non-Functional Requirements
- **Performance:** API responses <200ms.  
- **Availability:** 99.9% uptime SLA.  
- **Accessibility:** WCAG 2.1 compliance.  
- **Security:** TLS 1.2+, RBAC.  
- **Scalability:** 50+ HOAs, 10k+ users.  

---

## 10. MVP Roadmap
**Deliverables (Phase 1):**  
- Firebase Auth login + tenant handling  
- Multi-tenant setup  
- Financials: invoicing, payments, GL, budgets/reports, Plaid  
- Email + Push notifications  
- Document storage (S3)  
- Owner portal with PDF invoice viewing  

**Deferred (Phase 2+):**  
- OCR/AI ingestion  
- WhatsApp/SMS/Voice comms  
- Violation management  
- Voting & Surveys  
- Vendor payables  
