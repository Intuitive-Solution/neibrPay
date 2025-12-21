import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../views/Dashboard.vue';
import UnifiedAuth from '../views/UnifiedAuth.vue';
import TermsOfService from '../views/TermsOfService.vue';
import PrivacyNotice from '../views/PrivacyNotice.vue';
import MagicLinkAuth from '../views/MagicLinkAuth.vue';
import Invoices from '../views/Invoices.vue';
import AddInvoice from '../views/AddInvoice.vue';
import InvoiceDetail from '../views/InvoiceDetail.vue';
import Charges from '../views/Charges.vue';
import AddCharge from '../views/AddCharge.vue';
import People from '../views/People.vue';
import AddResident from '../views/AddResident.vue';
import Units from '../views/Units.vue';
import AddUnit from '../views/AddUnit.vue';
import Payments from '../views/Payments.vue';
import Expenses from '../views/Expenses.vue';
import AddExpense from '../views/AddExpense.vue';
import ExpenseDetail from '../views/ExpenseDetail.vue';
import Vendors from '../views/Vendors.vue';
import AddVendor from '../views/AddVendor.vue';
import Documents from '../views/Documents.vue';
import Settings from '../views/Settings.vue';
import Announcements from '../views/Announcements.vue';
import AddAnnouncement from '../views/AddAnnouncement.vue';
import Transactions from '../views/Transactions.vue';
import Budget from '../views/Budget.vue';
import { setupAuthGuards } from './guards';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/auth',
      name: 'UnifiedAuth',
      component: UnifiedAuth,
      meta: { requiresAuth: false },
    },
    {
      path: '/magic-link',
      name: 'MagicLinkAuth',
      component: MagicLinkAuth,
      meta: { requiresAuth: false },
    },
    {
      path: '/',
      name: 'Dashboard',
      component: Dashboard,
      meta: { requiresAuth: true },
    },
    {
      path: '/dashboard',
      redirect: '/',
    },
    {
      path: '/invoices',
      name: 'Invoices',
      component: Invoices,
      meta: { requiresAuth: true },
    },
    {
      path: '/invoices/create',
      name: 'AddInvoice',
      component: AddInvoice,
      meta: { requiresAuth: true },
    },
    {
      path: '/invoices/:id',
      name: 'InvoiceDetail',
      component: InvoiceDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/invoices/:id/edit',
      name: 'EditInvoice',
      component: AddInvoice,
      meta: { requiresAuth: true },
    },
    {
      path: '/charges',
      name: 'Charges',
      component: Charges,
      meta: { requiresAuth: true },
    },
    {
      path: '/charges/create',
      name: 'AddCharge',
      component: AddCharge,
      meta: { requiresAuth: true },
    },
    {
      path: '/charges/:id/edit',
      name: 'EditCharge',
      component: AddCharge,
      meta: { requiresAuth: true },
    },
    {
      path: '/people',
      name: 'People',
      component: People,
      meta: { requiresAuth: true },
    },
    {
      path: '/people/add',
      name: 'AddResident',
      component: AddResident,
      meta: { requiresAuth: true },
    },
    {
      path: '/people/edit/:id',
      name: 'EditResident',
      component: AddResident,
      meta: { requiresAuth: true },
    },
    {
      path: '/units',
      name: 'Units',
      component: Units,
      meta: { requiresAuth: true },
    },
    {
      path: '/units/add',
      name: 'AddUnit',
      component: AddUnit,
      meta: { requiresAuth: true },
    },
    {
      path: '/units/edit/:id',
      name: 'EditUnit',
      component: AddUnit,
      meta: { requiresAuth: true },
    },
    {
      path: '/payments',
      name: 'Payments',
      component: Payments,
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses',
      name: 'Expenses',
      component: Expenses,
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses/create',
      name: 'AddExpense',
      component: AddExpense,
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses/:id',
      name: 'ExpenseDetail',
      component: ExpenseDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses/:id/edit',
      name: 'EditExpense',
      component: AddExpense,
      meta: { requiresAuth: true },
    },
    {
      path: '/vendors',
      name: 'Vendors',
      component: Vendors,
      meta: { requiresAuth: true },
    },
    {
      path: '/vendors/create',
      name: 'AddVendor',
      component: AddVendor,
      meta: { requiresAuth: true },
    },
    {
      path: '/vendors/:id/edit',
      name: 'EditVendor',
      component: AddVendor,
      meta: { requiresAuth: true },
    },
    {
      path: '/documents',
      name: 'Documents',
      component: Documents,
      meta: { requiresAuth: true },
    },
    {
      path: '/announcements',
      name: 'Announcements',
      component: Announcements,
      meta: { requiresAuth: true },
    },
    {
      path: '/announcements/create',
      name: 'AddAnnouncement',
      component: AddAnnouncement,
      meta: { requiresAuth: true },
    },
    {
      path: '/announcements/:id/edit',
      name: 'EditAnnouncement',
      component: AddAnnouncement,
      meta: { requiresAuth: true },
    },
    {
      path: '/settings',
      name: 'Settings',
      component: Settings,
      meta: { requiresAuth: true },
    },
    {
      path: '/transactions',
      name: 'Transactions',
      component: Transactions,
      meta: { requiresAuth: true },
    },
    {
      path: '/budget',
      name: 'Budget',
      component: Budget,
      meta: { requiresAuth: true },
    },
    {
      path: '/terms-of-service',
      name: 'TermsOfService',
      component: TermsOfService,
      meta: { requiresAuth: false },
    },
    {
      path: '/privacy-notice',
      name: 'PrivacyNotice',
      component: PrivacyNotice,
      meta: { requiresAuth: false },
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
});

// Note: setupAuthGuards will be called in main.ts after Pinia is initialized
// This prevents "getActivePinia()" error

export default router;
