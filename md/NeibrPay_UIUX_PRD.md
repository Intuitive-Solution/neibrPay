# NeibrPay UI / UX Guideline & Design PRD

## 1. Design Vision & Principles

### 1.1 Vision
NeibrPay should feel **professional, trustworthy, clean, friendly**, reflecting both financial rigor and community spirit. Inspired by Wrike’s balanced use of whitespace, modern typography, subtle accent colors, and clear visual hierarchy.

### 1.2 Core Principles
- **Clarity**: Interfaces should communicate actions and states clearly (no visual noise).  
- **Consistency**: Components, spacing, typography, button styles must be uniform across modules.  
- **Hierarchy & Focus**: Use color, size, weight to guide attention to critical elements (invoices, alerts, actions).  
- **Accessibility**: Meet **WCAG 2.1 AA** level (contrast, keyboard, labels).  
- **Scalable Design System**: Build atomic components that can evolve (buttons, cards, forms, modals).

---

## 2. Brand, Colors & Typography (inspired by Wrike)

### 2.1 Branding & Color Palette

| Role | Color Example | Usage |
|---|---|---|
| **Primary Accent / Action** | Deep Teal / Blue‑Green (#0F7C8C) | Buttons, links, active elements |
| **Secondary Accent** | Soft Sky Blue (#3FB4C5) | Hover states, secondary elements |
| **Neutral / Background** | Off‑white (#F9FAFB), Light Gray (#E5E7EB) | Page backgrounds, card backgrounds |
| **Text – Primary** | Dark Slate (#242D38) | Headings, body text |
| **Text – Secondary** | Gray (#6B7280) | Subtext, labels |
| **Error / Danger** | Coral / Soft Red (#E55353) | Validation errors, alerts |
| **Success / Notification** | Teal / Green (#34C38F) | Success messages, positive feedback |

### 2.2 Typography

- **Font Family**: Use a modern sans-serif (e.g. *Inter*, *Roboto*, *Helvetica Neue*).  
- **Font Weights & Sizes**:

| Use | Font Size | Weight |
|---|---|---|
| Heading 1 / Page Title | 28–32px | 600 |
| Heading 2 | 24px | 600 |
| Heading 3 / Section Header | 20px | 500 |
| Body | 16px | 400 |
| Small / Caption / Labels | 14px | 400 |

- **Line heights / spacing**: 1.4–1.6 for body text; 1.3 for headings.  
- **Letter spacing**: Slight tracking for uppercase labels (~0.5px).

---

## 3. Component Library / UI Patterns

| Component | State Variants | Key Considerations |
|---|---|---|
| **Buttons** | Default, Hover, Active, Disabled | Use primary accent for main actions; secondary / outline for secondary. |
| **Inputs / TextFields** | Focus, Error, Disabled, Filled | Clear label, placeholder, inline validation in red. |
| **Select / Dropdowns** | Open, Closed, Disabled | Consistent padding, arrow icon, grouping support. |
| **Cards / Panels** | Default, Hover, Elevated | Shadow / border for separation. |
| **Modals / Dialogs** | Overlay + centered panel | Dark translucent backdrop, “x” close button. |
| **Tables / Lists** | Header row, striped rows, hover highlight | Sorting arrows, row actions. |
| **Notifications / Alerts** | Success, Error, Info | Toast banners; icon + message. |
| **PDF Viewer / Invoice Preview** | Embedded view, download / print | pdf.js rendering, toolbar actions. |
| **Sidebar / Navigation** | Collapsible, active states | Icons + labels; highlight current section. |
| **Topbar / Header** | User avatar, notifications, breadcrumbs | Clean spacing, key actions. |

---

## 4. Layout & Spacing System
- Base spacing unit: **8px**.  
- Use a **12‑column grid** (max width ~1200px).  
- Page margin: 24px; card padding: 16px.  
- Vertical rhythm: 24px between sections.

---

## 5. Navigation & Information Architecture
- **Sidebar**: Dashboard, Invoices, Reports, Documents, Announcements, Settings.  
- **Topbar**: User menu (profile, logout), notifications.  
- **Breadcrumbs**: Show navigation path.  
- **Tabs**: Sub‑navigation inside modules (e.g. Invoices: All / Pending / Paid).

---

## 6. UX Flows (Key Screens)

### 6.1 Onboarding
- Board admin signs up → create HOA → invite members → dashboard.

### 6.2 Invoice Creation & Viewing
- Create invoice → preview PDF inline → send.  
- Invoice list → click to view → embedded PDF with download/print.

### 6.3 Owner Portal
- Dashboard: dues, announcements, quick pay.  
- Past invoices: PDF inline view + download.  
- Documents: list grouped by year & category.

### 6.4 Announcement Flow
- Admin creates announcement → preview → send via email + push.

---

## 7. Visual States & Feedback
- **Loading states / Skeletons** for tables, cards.  
- **Disabled states** dimmed.  
- **Hover/active**: subtle background change.  
- **Error/validation**: red border + inline message.  
- **Success**: green toast banner.  
- **Empty states**: illustration + text guidance.

---

## 8. Accessibility & Usability
- Contrast ratio ≥ 4.5:1.  
- Click targets ≥ 44×44 px.  
- Keyboard nav: tab order, Enter triggers, ESC closes.  
- Screen reader labels.  
- Alt text for icons/images.  
- Descriptive link texts.

---

## 9. Responsive / Mobile
- Collapse sidebar to hamburger.  
- Full‑width cards.  
- PDF viewer scrollable/pinchable.  
- Bottom nav if needed.  
- Touch‑friendly toggles.

---

## 10. Design Deliverables
- **Design Tokens**: colors, spacing, typography.  
- **Component Library** (Figma): buttons, inputs, cards, modals, tables.  
- **Hi‑fi mocks**: Dashboard, Invoices, Owner Portal, Docs, Announcements.  
- **Interaction specs**: hover, loading, disabled states.  
- **Assets**: icons (SVG), empty‑state illustrations.  
- **Style guide**: logo, tone, brand rules.  
