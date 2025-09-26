# NeibrPay — Owner Portal PRD (MVP)

## 1. Objective
Provide homeowners a **secure, self-service portal** to manage their dues, payments, documents, and community communication.  
The portal must be **simple, mobile-responsive, and intuitive**.

---

## 2. Target Role
- **Owners / Residents** (end-users invited by HOA board).  
- Must be accessible on web and mobile (responsive design).  

---

## 3. Features (MVP)

### 3.1 Dashboard (Owner Landing Page)
- **Dues Summary**:  
  - Outstanding balance  
  - Next payment due date  
  - Quick pay button  
- **Recent Payments**: Last 5 payments with amount and status.  
- **Announcements**: Most recent notices from HOA.  

### 3.2 Invoices & Payments
- **Invoices List**:  
  - Status: unpaid, overdue, paid  
  - Due date, amount, invoice number  
- **Invoice Detail**:  
  - Embedded PDF view (using `pdf.js`)  
  - Download/print option  
  - Payment history against invoice  
- **Make Payment**:  
  - Pay full or partial dues via Stripe (ACH/card)  
  - Save payment method (optional Phase 2)  
- **Payment History**:  
  - List of all past payments, methods, reference numbers  

### 3.3 Documents
- Access community documents shared with owners:  
  - Categories: bylaws, minutes, notices, financials  
  - View PDF inline or download  
  - Search/filter documents by year/category  

### 3.4 Announcements
- List of announcements with:  
  - Title, body, published date  
  - Push/email notifications link to portal view  

### 3.5 Profile & Settings
- View/edit personal details (name, email, phone).  
- Manage notification preferences (email, push).  
- View assigned property (unit/lot).  

---

## 4. Non-Functional Requirements
- **Mobile-first design** (cards stack, collapsible menus).  
- **Performance**: Dashboard loads in < 2s.  
- **Security**:  
  - Firebase Auth required for login.  
  - Owners see only their own dues/payments.  
- **Accessibility**: WCAG 2.1 AA compliance.  

---

## 5. Data Model Impact
Tables queried/used by owner portal:  
- `invoices` (filtered by `user_id`)  
- `payments` (filtered by `user_id`)  
- `documents` (owner-visible only)  
- `announcements` (all active tenant announcements)  
- `property_owners` (to display unit/lot info)  
- `users` (profile details)  

---

## 6. UI/UX Guidelines
- Clean, simple navigation with 3–4 main tabs:  
  - Dashboard  
  - Invoices & Payments  
  - Documents  
  - Announcements  
  - Profile (settings)  
- **Primary Actions** always visible:  
  - “Pay Now” button in header if dues outstanding.  
- Use **cards & lists** for clarity.  
- Friendly empty states:  
  - “No outstanding dues 🎉”  
  - “No announcements right now.”  

---

## 7. Integrations
- **Firebase Auth** (owners log in via invite + registration).  
- **Stripe Payments** (ACH, cards).  
- **AWS S3** (document storage).  
- **Firebase Cloud Messaging** (push notifications).  

---

## 8. Deliverables (Phase 1)
- Owner Dashboard page  
- Invoice list + PDF view + payment  
- Payment history  
- Document center (filtered for owner-visible docs)  
- Announcements list  
- Profile & preferences page  

---

## 9. Deferred Features (Future Phases)
- Save payment method / auto-pay  
- Online voting & surveys  
- Maintenance/architectural request submission  
- Violation notices & dispute resolution  
- Multi-property owner support  
- Usage analytics for HOA board  

---
