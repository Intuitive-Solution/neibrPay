# NeibrPay — HOA Onboarding PRD (MVP)

## 1. Objective
Provide a **streamlined, guided onboarding flow** that allows a self-managed HOA (tenant) to set up their community in NeibrPay quickly.  
The goal is to ensure that a **board admin** can move from signup → setup → ready-to-use dashboard **in under 15 minutes**.

---

## 2. Scope (MVP Onboarding)

- **Tenant Creation**: Create HOA entity in system (multi-tenant setup).  
- **Board Admin Setup**: Link Firebase-authenticated user as first `board_admin`.  
- **Basic HOA Info Capture**: Name, address, fiscal year start.  
- **Initial Property Setup**: Import (CSV/manual) of units/lots.  
- **Member Invitation**: Invite owners and other board members.  
- **Default Financial Setup**: Auto-seed GL accounts, budgets optional.  
- **Bank Connection** (optional in MVP): Link bank account via Plaid or manual.  
- **Quick Start Checklist**: Dashboard checklist guiding admin to first actions.  

---

## 3. Actors & Roles

- **Board Admin (Onboarder)**: initiates setup, provides info, invites members.  
- **System (NeibrPay)**: auto-creates tenant, seeds financial data, configures portal.  
- **Owners (Invited)**: join later via invitation.  

---

## 4. Onboarding Flow (Step-by-Step)

### Step 1 — Account Creation
- User signs up via **Firebase Auth** (email + social login).  
- Email verification required.  
- First user is designated as **board_admin**.

### Step 2 — HOA / Tenant Details
- Capture:
  - HOA name (legal & display name)  
  - Address (line1, line2, city, state, postal, country)  
  - Fiscal year start (month)  
- Generate `slug` (e.g., green-oaks-condo.neibrpay.com).

### Step 3 — Property Setup
- Option A: **Manual Entry** — add units/lots one by one (code, address).  
- Option B: **CSV Upload** — template with unit codes, owners (optional).  
- Validation: ensure codes are unique within tenant.  

### Step 4 — Owner & Board Invitations
- Enter emails of board members & owners (CSV or inline form).  
- Invitations created → `invitations` table.  
- Emails sent with secure token links.  
- Roles assigned (board_admin, owner).  

### Step 5 — Financial Setup
- Auto-seed **default GL chart of accounts** (assets, liabilities, equity, income, expense).  
- Option to set **annual budget** (optional in MVP).  
- Default billing cycle config (e.g., monthly dues amount).  

### Step 6 — Bank Account (Optional MVP)
- Option to **connect Plaid** or add manual bank account (name, institution, mask).  
- Can be skipped.  

### Step 7 — Quick Start Checklist
Dashboard shows checklist:  
- [ ] Invite board members  
- [ ] Add properties  
- [ ] Send first invoice  
- [ ] Upload bylaws/documents  
- [ ] Connect bank account  

Checklist progress shown on admin dashboard.

---

## 5. Data Model Impact

Tables touched during onboarding:  
- `users` (new Firebase user)  
- `tenants` (new HOA created)  
- `memberships` (link board_admin user → tenant)  
- `properties` (initial units/lots)  
- `invitations` (pending owners/board invites)  
- `gl_accounts` (seed chart of accounts)  
- `bank_accounts` (if added)  

---

## 6. UX/UI Guidelines (Onboarding)

- **Guided Wizard (6 steps)** with progress bar.  
- **Save & Continue Later** option.  
- Validation at each step; helpful tooltips (e.g., “Fiscal year determines your reporting cycle”).  
- Friendly language (“Welcome! Let’s set up your community in just a few minutes”).  
- Empty-state illustrations for clarity.  

---

## 7. Integrations

- **Firebase Auth**: user identity.  
- **SendGrid/SES (via Laravel Mail)**: onboarding invitation emails.  
- **Plaid API (optional)**: bank link.  

---

## 8. Security & Compliance

- Verify email before creating HOA tenant.  
- Limit one tenant per new user during initial setup (can join others via invite).  
- Role-based access: only `board_admin` can onboard.  
- Invitations: tokens expire after 7 days.  

---

## 9. Non-Functional Requirements

- **Performance**: onboarding steps load < 200ms each.  
- **Reliability**: onboarding must auto-recover from partial completion (resume flow).  
- **Scalability**: support hundreds of HOAs onboarding concurrently.  
- **Audit**: log all tenant creation, property imports, and invitations.  

---

## 10. Deliverables (Phase 1)

- Multi-step **onboarding wizard** (frontend Vue 3).  
- Backend endpoints (Laravel) for each step:
  - `POST /tenants` (create HOA)  
  - `POST /properties/import` (CSV upload)  
  - `POST /invitations` (send invites)  
  - `POST /bank_accounts` (optional)  
- Seeder for GL accounts.  
- Dashboard checklist component.  
