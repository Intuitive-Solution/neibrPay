# NeibrPay MVP — Task Breakdown

## Phase 0 — Project Setup

- [*] Repo setup (monorepo)
- [*] CI/CD pipeline (GitHub Actions, staging deployment)
- [*] Laravel + Vue 3 boilerplate setup
- [*] DB architecture (tenant_id scoping)
- [*] Database migrations scaffolding

---

## Phase 1 — Authentication & Access Control

- [*] Integrate Firebase Auth (email + social logins)
- [*] Role-based access (board_admin, bookkeeper, owner)
- [*] Invitation system (email-based for owners/board)
- [*] Session & token handling (Firebase JWT)
- [*] Role-aware middleware in Laravel

---

## Phase 2 — Onboarding Flow

- [ ] Multi-step wizard (Vue) with progress bar
- [ ] Tenant creation (HOA details)
- [ ] Property setup (manual + CSV import)
- [ ] Member invitations
- [ ] Financial setup (GL seeding, dues defaults)
- [ ] Optional bank connection (Plaid/manual)
- [ ] Resume onboarding mid-way (progress save)

---

## Phase 3 — HOA Manager Dashboard

- [ ] Dashboard UI (Vue + Vuetify)
- [ ] Financial Overview widget
- [ ] Quick Actions (create invoice, record payment, add expense, upload doc, announcement)
- [ ] Outstanding Dues table
- [ ] Recent Payments activity
- [ ] Pending Vendor Bills
- [ ] Announcements panel
- [ ] Quick Start Checklist

---

## Phase 4 — Owner Portal

- [ ] Owner dashboard (summary of dues, quick pay)
- [ ] Invoice list + detail (PDF.js embed)
- [ ] Stripe payment integration (ACH, card)
- [ ] Payment history page
- [ ] Document center (owner-visible docs)
- [ ] Announcements feed
- [ ] Profile & preferences (notifications)

---

## Phase 5 — Financials (Core Accounting)

- [ ] Invoice CRUD + send
- [ ] Invoice items
- [ ] Payments (Stripe/manual entry)
- [ ] Payment allocations
- [ ] Expenses (vendor bills)
- [ ] Vendor management (directory, payment terms)
- [ ] Vendor payments (ACH/check/card/wire)
- [ ] Journal entries auto-posting
- [ ] GL accounts CRUD (seeded + custom)
- [ ] Budgets CRUD

---

## Phase 6 — Documents & Communication

- [ ] Document upload (S3, categories)
- [ ] Document view/download (permissions)
- [ ] Announcements (create, broadcast email + push)
- [ ] Email notifications (SendGrid/SES)
- [ ] Push notifications (Firebase Cloud Messaging)

---

## Phase 7 — Reporting & Audit

- [ ] Reports: Income vs Expenses, Budget vs Actual, A/R Aging, A/P Aging
- [ ] Export CSV/PDF
- [ ] Audit logs (invoices, payments, expenses, users)

---

## Phase 8 — Integrations

- [ ] Stripe setup (customers, payments)
- [ ] Plaid integration (optional MVP)
- [ ] Webhooks (Stripe, Plaid)
- [ ] Bank reconciliation (import + match transactions)

---

## Phase 9 — Infrastructure & Security

- [ ] Multi-tenant DB scope enforcement
- [ ] S3 storage with pre-signed URLs
- [ ] Encryption at rest (sensitive fields)
- [ ] Role-based API authorization
- [ ] Rate limiting & monitoring
- [ ] DB + S3 backups

---

## Phase 10 — QA, Testing, Launch

- [ ] Unit tests (Laravel + Vue)
- [ ] Integration tests (API contracts)
- [ ] End-to-end tests (Cypress/Playwright)
- [ ] Performance testing (1k HOAs, 10k invoices)
- [ ] Security review (auth, file access, PCI)
- [ ] Staging → Production deployment
- [ ] Early adopter onboarding

---

## Optional (Future)

- [ ] Online voting & surveys
- [ ] Maintenance request workflows
- [ ] Violation tracking
- [ ] Auto-pay (recurring payments)
- [ ] Native mobile apps (Capacitor/Ionic or RN wrapper)
