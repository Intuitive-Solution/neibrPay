# NeibrPay — HOA Manager Dashboard PRD (MVP)

## 1. Objective
Provide a **centralized dashboard** for HOA managers (Board Admins) to monitor finances, operations, and communications at a glance.  
The dashboard should be **action-oriented**, highlighting dues, pending tasks, and recent activity.

---

## 2. Target Role
- **Board Admins** (primary user): Manage financials, documents, communications, vendors, and compliance.  
- Secondary: **Bookkeepers** (see financial sections only).  

---

## 3. Dashboard Layout

### 3.1 Page Structure
- **Topbar**: Tenant name/logo, search, notifications, user menu.  
- **Sidebar (Primary Navigation)**:  
  - Dashboard (home)  
  - Invoices & Payments  
  - Expenses & Vendors  
  - Properties & Owners  
  - Documents  
  - Announcements  
  - Reports  
  - Settings  

### 3.2 Main Dashboard Widgets
1. **Financial Overview (Card/Grid)**  
   - Total dues billed this month  
   - Total collected vs outstanding  
   - Accounts payable (vendor bills due)  
   - Bank balance (from linked account)  

2. **Action Items / Quick Links**  
   - Create Invoice  
   - Record Payment  
   - Add Expense / Vendor  
   - Upload Document  
   - Send Announcement  

3. **Outstanding Dues Table**  
   - List of top 10 overdue invoices (owner, amount, due date).  

4. **Recent Payments Activity**  
   - List of latest payments received (payer, method, amount).  

5. **Pending Vendor Bills**  
   - Vendor, due date, amount, status.  

6. **Announcements / Communication Feed**  
   - Most recent community announcements.  

7. **Checklist (Onboarding/Tasks)** (if not completed)  
   - Setup reminders: e.g., “Connect Bank”, “Send First Invoice”.  

---

## 4. Menu Structure (Sidebar Navigation)

### 4.1 Invoices & Payments
- Create new invoice  
- View invoices (filter: all, unpaid, overdue, paid)  
- Record payment (manual)  
- Payment history  

### 4.2 Expenses & Vendors
- Add vendor  
- Enter expense (vendor bill)  
- View expenses (draft, approved, paid)  
- Vendor list & profiles  
- Vendor payments  

### 4.3 Properties & Owners
- Add/edit properties (units/lots)  
- Assign owners (link to users)  
- Owner list with status (active/inactive, dues balance)  
- Import owners via CSV  

### 4.4 Documents
- Upload community documents (bylaws, minutes, notices)  
- Organize into folders/categories  
- Permissions: board-only vs owner-visible  

### 4.5 Announcements
- Create new announcement (send email + push)  
- View past announcements  
- Engagement stats (delivered/opened) [Phase 2]  

### 4.6 Reports
- Financial summary (income vs expenses)  
- Budget vs actual (if budget uploaded)  
- Accounts receivable aging  
- Accounts payable aging  
- Export CSV/PDF  

### 4.7 Settings
- HOA info (name, address, fiscal year)  
- Billing cycle & dues defaults  
- Roles & permissions (manage memberships)  
- Bank accounts (Plaid/manual)  
- Stripe integration  

---

## 5. Operations Supported (Board Admin)

- **Financial Management**  
  - Issue invoices, track payments, manage GL, reconcile banks.  
- **Payables**  
  - Enter vendor bills, approve/void, make payments.  
- **Community Directory**  
  - Manage owners and property records.  
- **Governance & Documents**  
  - Upload/share governing docs, track versions.  
- **Communication**  
  - Send announcements to owners (email + push).  
- **Compliance & Audits**  
  - Access audit logs, review financial reports.  

---

## 6. Role-Based Access

| Menu/Action | Board Admin | Bookkeeper | Owner |
|-------------|-------------|------------|-------|
| Dashboard | ✅ | ✅ (financial cards only) | ❌ |
| Invoices & Payments | ✅ | ✅ | ❌ |
| Expenses & Vendors | ✅ | ✅ | ❌ |
| Properties & Owners | ✅ | ❌ | ❌ |
| Documents | ✅ | ❌ | View-only (owner portal) |
| Announcements | ✅ | ❌ | View-only |
| Reports | ✅ | ✅ | ❌ |
| Settings | ✅ | ❌ | ❌ |

---

## 7. UX/UI Guidelines
- **Card-based layout** for quick visual scan.  
- **Charts**: simple bar/pie for collected vs outstanding dues.  
- **Tables**: sortable, paginated, with filters.  
- **Call-to-action buttons** (primary color) for quick links.  
- **Notification center**: push/email logs.  
- **Empty states**: illustrations with guidance (“No invoices yet — create your first invoice”).  

---

## 8. Non-Functional Requirements
- Dashboard loads under **2s** with cached summaries.  
- Secure data by tenant ID.  
- Real-time update for payments (via Stripe webhook).  
- Mobile responsive (cards stack).  

---

## 9. Deliverables (Phase 1)
- Dashboard page (Vue 3 + Vuetify)  
- Widgets: Financial overview, action items, tables  
- Sidebar navigation with role-based rendering  
- Backend endpoints (Laravel) for dashboard data:  
  - `GET /dashboard/summary` (financials, dues, payables)  
  - `GET /invoices/overdue`  
  - `GET /payments/recent`  
  - `GET /expenses/pending`  
  - `GET /announcements/recent`  
- Role-based access middleware  

---
