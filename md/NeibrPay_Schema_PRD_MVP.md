# NeibrPay — Database Schema PRD (MVP, Schema Only)

This document specifies only the **database schema** for NeibrPay MVP. All entities are **tenant-scoped** unless noted.

---

## Conventions
- **Primary Keys:** `ulid` (Laravel 11 ULIDs).
- **Foreign Keys:** `*_id` (ulid).
- **Tenant scope:** every business table includes `tenant_id`.
- **Timestamps:** `created_at`, `updated_at`; **soft delete** with `deleted_at` where indicated.
- **Money:** `decimal(12,2)` in USD.
- **Enums:** PostgreSQL enums (or string with check constraints).
- **Indexes:** On FKs, `tenant_id`, and common filters (`status`, `due_date`).
- **Audit:** Critical changes should write to `audit_logs` (app layer).

---

## 1) Core Identity & Tenancy

### 1.1 `users`
- `id` ulid pk  
- `firebase_uid` string(128) unique  
- `email` string(255) index  
- `name` string(120)  
- `phone` string(32) nullable  
- `email_verified` boolean  
- `mfa_enabled` boolean  
- `last_login_at` timestamp nullable  
- `created_at` / `updated_at` timestamps

### 1.2 `tenants`
- `id` ulid pk  
- `name` string(160)  
- `slug` string(120) unique  
- `status` enum: `active`, `suspended`  
- `address_line1` string(160) nullable  
- `address_line2` string(160) nullable  
- `city` string(120) nullable  
- `state` string(80) nullable  
- `postal` string(20) nullable  
- `country` string(2) (ISO‑2) default 'US'  
- `created_by` ulid → users.id  
- `created_at` / `updated_at` timestamps

### 1.3 `memberships`
- `id` ulid pk  
- `tenant_id` ulid → tenants.id index  
- `user_id` ulid → users.id index  
- `role` enum: `owner`, `board_admin`, `bookkeeper`  
- `invited_by` ulid → users.id nullable  
- `created_at` / `updated_at` timestamps  
- **unique** (`tenant_id`, `user_id`)

### 1.4 `invitations`
- `id` ulid pk  
- `tenant_id` ulid → tenants.id  
- `email` string(255) index  
- `role` enum: `owner`, `board_admin`, `bookkeeper`  
- `token` string(128) unique  
- `expires_at` timestamp  
- `accepted_at` timestamp nullable  
- `created_by` ulid → users.id  
- `created_at` / `updated_at` timestamps

### 1.5 `devices`
- `id` ulid pk  
- `user_id` ulid → users.id index  
- `tenant_id` ulid → tenants.id index  
- `fcm_token` text  
- `platform` enum: `web`, `ios`, `android`  
- `last_seen_at` timestamp  
- `created_at` / `updated_at` timestamps

---

## 2) Directory (Units/Lots & Ownership)

### 2.1 `properties`
- `id` ulid pk  
- `tenant_id` ulid index  
- `code` string(64) index (e.g., "A‑101")  
- `address_line1` string(160) nullable  
- `address_line2` string(160) nullable  
- `city` string(120) nullable  
- `state` string(80) nullable  
- `postal` string(20) nullable  
- `status` enum: `active`, `inactive`  
- `created_at` / `updated_at` timestamps

### 2.2 `property_owners`
- `id` ulid pk  
- `tenant_id` ulid index  
- `property_id` ulid → properties.id index  
- `user_id` ulid → users.id index  
- `ownership_pct` decimal(5,2) nullable  
- `is_primary` boolean default false  
- `started_at` date nullable  
- `ended_at` date nullable  
- `created_at` / `updated_at` timestamps  
- **unique** (`tenant_id`, `property_id`, `user_id`)

---

## 3) Invoicing & Payments

### 3.1 `invoices`
- `id` ulid pk  
- `tenant_id` ulid index  
- `number` string(40) index  
- `property_id` ulid → properties.id nullable  
- `user_id` ulid → users.id index  
- `status` enum: `draft`, `issued`, `overdue`, `paid`, `void`  
- `issue_date` date  
- `due_date` date  
- `currency` string(3) default 'USD'  
- `subtotal` decimal(12,2)  
- `tax_total` decimal(12,2)  
- `discount_total` decimal(12,2)  
- `total` decimal(12,2)  
- `balance_due` decimal(12,2)  
- `notes` text nullable  
- `pdf_url` text nullable  
- `created_by` ulid → users.id  
- `created_at` / `updated_at` timestamps  
- `deleted_at` timestamp nullable

### 3.2 `invoice_items`
- `id` ulid pk  
- `invoice_id` ulid → invoices.id index  
- `line_no` integer  
- `description` string(255)  
- `qty` decimal(12,3)  
- `unit_price` decimal(12,2)  
- `tax_rate` decimal(5,2)  
- `amount` decimal(12,2)  
- `created_at` / `updated_at` timestamps

### 3.3 `payments`
- `id` ulid pk  
- `tenant_id` ulid index  
- `invoice_id` ulid → invoices.id index nullable  
- `user_id` ulid → users.id index  
- `method` enum: `card`, `ach`, `check`, `cash`  
- `amount` decimal(12,2)  
- `status` enum: `pending`, `succeeded`, `failed`, `refunded`  
- `received_at` timestamp  
- `stripe_payment_intent` string(128) nullable  
- `stripe_charge_id` string(128) nullable  
- `reference` string(64) nullable  
- `created_at` / `updated_at` timestamps

### 3.4 `payment_allocations`
- `id` ulid pk  
- `payment_id` ulid → payments.id index  
- `invoice_id` ulid → invoices.id index  
- `amount` decimal(12,2)  
- `created_at` / `updated_at` timestamps

---

## 4) General Ledger (GL) & Budgeting

### 4.1 `gl_accounts`
- `id` ulid pk  
- `tenant_id` ulid index  
- `code` string(32) index  
- `name` string(160)  
- `type` enum: `asset`, `liability`, `equity`, `income`, `expense`  
- `is_active` boolean default true  
- `created_at` / `updated_at` timestamps  
- **unique** (`tenant_id`, `code`)

### 4.2 `journal_entries`
- `id` ulid pk  
- `tenant_id` ulid index  
- `entry_no` string(40) index  
- `entry_date` date  
- `memo` string(255) nullable  
- `source` enum: `invoice`, `payment`, `manual`, `bank_recon`, `expense`, `vendor_payment`  
- `source_id` ulid nullable  
- `posted_by` ulid → users.id  
- `created_at` / `updated_at` timestamps

### 4.3 `journal_lines`
- `id` ulid pk  
- `journal_entry_id` ulid → journal_entries.id index  
- `gl_account_id` ulid → gl_accounts.id index  
- `debit` decimal(12,2) default 0  
- `credit` decimal(12,2) default 0  
- `line_memo` string(255) nullable

### 4.4 `budgets`
- `id` ulid pk  
- `tenant_id` ulid index  
- `fiscal_year` integer  
- `name` string(120)  
- `created_by` ulid → users.id  
- `created_at` / `updated_at` timestamps  
- **unique** (`tenant_id`, `fiscal_year`)

### 4.5 `budget_lines`
- `id` ulid pk  
- `budget_id` ulid → budgets.id index  
- `gl_account_id` ulid → gl_accounts.id index  
- `period` smallint check 1..12  
- `amount` decimal(12,2)  
- `created_at` / `updated_at` timestamps  
- **unique** (`budget_id`, `gl_account_id`, `period`)

---

## 5) Banking & Reconciliation (Plaid)

### 5.1 `bank_accounts`
- `id` ulid pk  
- `tenant_id` ulid index  
- `name` string(120)  
- `institution` string(160)  
- `plaid_item_id` string(128) nullable  
- `plaid_account_id` string(128) nullable  
- `mask` string(8) nullable  
- `currency` string(3) default 'USD'  
- `created_at` / `updated_at` timestamps

### 5.2 `bank_transactions`
- `id` ulid pk  
- `tenant_id` ulid index  
- `bank_account_id` ulid → bank_accounts.id index  
- `posted_at` date  
- `description` string(255)  
- `amount` decimal(12,2)  
- `external_id` string(128) index  
- `status` enum: `imported`, `matched`, `reconciled`, `ignored`  
- `matched_invoice_id` ulid → invoices.id nullable  
- `matched_payment_id` ulid → payments.id nullable  
- `created_at` / `updated_at` timestamps

### 5.3 `reconciliations`
- `id` ulid pk  
- `tenant_id` ulid index  
- `bank_account_id` ulid  
- `statement_start` date  
- `statement_end` date  
- `starting_balance` decimal(12,2)  
- `ending_balance` decimal(12,2)  
- `created_by` ulid → users.id  
- `created_at` / `updated_at` timestamps

### 5.4 `reconciliation_lines`
- `id` ulid pk  
- `reconciliation_id` ulid → reconciliations.id index  
- `bank_transaction_id` ulid → bank_transactions.id index  
- `status` enum: `cleared`, `uncleared`  
- `created_at` / `updated_at` timestamps

---

## 6) Vendors & Expenses (Payables)

### 6.1 `vendors`
- `id` ulid pk  
- `tenant_id` ulid index  
- `name` string(160)  
- `tax_id` string(32) nullable  
- `email` string(255) nullable  
- `phone` string(32) nullable  
- `website` string(255) nullable  
- `address_line1` string(160) nullable  
- `address_line2` string(160) nullable  
- `city` string(120) nullable  
- `state` string(80) nullable  
- `postal` string(20) nullable  
- `country` string(2) default 'US'  
- `default_gl_account_id` ulid → gl_accounts.id nullable  
- `payment_terms` string(40) nullable  
- `notes` text nullable  
- `is_active` boolean default true  
- `created_at` / `updated_at` timestamps  
- **unique** (`tenant_id`, `name`)

### 6.2 `expenses`
- `id` ulid pk  
- `tenant_id` ulid index  
- `vendor_id` ulid → vendors.id index  
- `number` string(60) nullable index  
- `status` enum: `draft`, `approved`, `scheduled`, `paid`, `void`  
- `bill_date` date  
- `due_date` date nullable  
- `currency` string(3) default 'USD'  
- `subtotal` decimal(12,2)  
- `tax_total` decimal(12,2)  
- `discount_total` decimal(12,2)  
- `total` decimal(12,2)  
- `balance_due` decimal(12,2)  
- `memo` string(255) nullable  
- `attachment_key` string(255) nullable  
- `entered_by` ulid → users.id  
- `approved_by` ulid → users.id nullable  
- `approved_at` timestamp nullable  
- `created_at` / `updated_at` timestamps  
- `deleted_at` timestamp nullable  
- **index** (`tenant_id`, `status`, `due_date`)

### 6.3 `expense_items`
- `id` ulid pk  
- `expense_id` ulid → expenses.id index  
- `line_no` integer  
- `description` string(255)  
- `qty` decimal(12,3) default 1  
- `unit_price` decimal(12,2)  
- `tax_rate` decimal(5,2)  
- `amount` decimal(12,2)  
- `gl_account_id` ulid → gl_accounts.id index  
- `property_id` ulid → properties.id nullable  
- `created_at` / `updated_at` timestamps

### 6.4 `vendor_payments`
- `id` ulid pk  
- `tenant_id` ulid index  
- `vendor_id` ulid → vendors.id index  
- `method` enum: `ach`, `check`, `card`, `wire`  
- `status` enum: `pending`, `succeeded`, `failed`, `void`  
- `amount` decimal(12,2)  
- `paid_at` timestamp  
- `reference` string(64) nullable  
- `bank_account_id` ulid → bank_accounts.id nullable  
- `memo` string(255) nullable  
- `created_by` ulid → users.id  
- `created_at` / `updated_at` timestamps

### 6.5 `vendor_payment_allocations`
- `id` ulid pk  
- `vendor_payment_id` ulid → vendor_payments.id index  
- `expense_id` ulid → expenses.id index  
- `amount` decimal(12,2)  
- `created_at` / `updated_at` timestamps

### 6.6 `vendor_1099_settings` (optional MVP+)
- `id` ulid pk  
- `tenant_id` ulid index  
- `vendor_id` ulid → vendors.id index  
- `is_1099_eligible` boolean default false  
- `type` enum: `nec`, `misc`, `none` default `none`  
- `w9_file_key` string(255) nullable  
- `created_at` / `updated_at` timestamps

---

## 7) Documents & Announcements

### 7.1 `documents`
- `id` ulid pk  
- `tenant_id` ulid index  
- `category` enum: `bylaws`, `minutes`, `notices`, `financials`, `other`  
- `title` string(160)  
- `file_key` string(255)  
- `mime_type` string(100)  
- `size_bytes` integer  
- `uploaded_by` ulid → users.id  
- `created_at` / `updated_at` timestamps  
- `deleted_at` timestamp nullable

### 7.2 `announcements`
- `id` ulid pk  
- `tenant_id` ulid index  
- `title` string(160)  
- `body` text  
- `published_at` timestamp nullable  
- `created_by` ulid → users.id  
- `created_at` / `updated_at` timestamps

---

## 8) Notifications & Audit

### 8.1 `notifications`
- `id` ulid pk  
- `tenant_id` ulid index nullable  
- `user_id` ulid → users.id nullable  
- `channel` enum: `email`, `push`  
- `template` string(80)  
- `subject` string(160) nullable  
- `payload` jsonb  
- `status` enum: `queued`, `sent`, `failed`  
- `error` text nullable  
- `sent_at` timestamp nullable  
- `created_at` / `updated_at` timestamps

### 8.2 `audit_logs`
- `id` ulid pk  
- `tenant_id` ulid index  
- `user_id` ulid → users.id nullable  
- `action` string(80)  
- `entity_type` string(80)  
- `entity_id` ulid nullable  
- `metadata` jsonb  
- `created_at` timestamp

---

## 9) Stripe & Webhooks

### 9.1 `stripe_customers`
- `id` ulid pk  
- `tenant_id` ulid index  
- `user_id` ulid → users.id index  
- `stripe_customer_id` string(128) unique  
- `created_at` / `updated_at` timestamps

### 9.2 `webhooks`
- `id` ulid pk  
- `provider` enum: `stripe`, `plaid`  
- `event_type` string(120)  
- `payload` jsonb  
- `processed_at` timestamp nullable  
- `created_at` timestamp

---

## 10) Seed GL (per tenant)
- **Assets:** 1000 Cash – Operating; 1100 A/R – Owners  
- **Liabilities:** 2100 A/P – Vendors; 2000 Unearned Revenue  
- **Equity:** 3000 Fund Balance  
- **Income:** 4000 Dues & Assessments; 4100 Late Fees  
- **Expense:** 5000 Maintenance; 5100 Utilities; 5200 Insurance; 5300 Admin

---

## 11) Key Constraints & Rules (App Layer)
- `invoice.total = sum(invoice_items.amount)`; `balance_due = total - sum(payment_allocations.amount)`  
- `journal_entries`: sum(debit) == sum(credit) per entry  
- `budget_lines.period` in 1..12  
- `expenses.total = sum(expense_items.amount)`; `balance_due = total - sum(vendor_payment_allocations.amount)`  
- Prevent over‑allocations and approving expenses without items

---

## 12) Indexing Recommendations
- `invoices(tenant_id, status, due_date)`  
- `payments(tenant_id, received_at)`  
- `bank_transactions(tenant_id, bank_account_id, posted_at)`  
- `expenses(tenant_id, status, due_date)`  
- Partial indexes for frequent `status` filters
