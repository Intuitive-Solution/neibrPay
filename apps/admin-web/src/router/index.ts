import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Login.vue';
import Signup from '../views/Signup.vue';
import ForgotPassword from '../views/ForgotPassword.vue';
import ResetPassword from '../views/ResetPassword.vue';
import TermsOfService from '../views/TermsOfService.vue';
import PrivacyNotice from '../views/PrivacyNotice.vue';
import Invoices from '../views/Invoices.vue';
import AddInvoice from '../views/AddInvoice.vue';
import InvoiceDetail from '../views/InvoiceDetail.vue';
import People from '../views/People.vue';
import AddResident from '../views/AddResident.vue';
import Units from '../views/Units.vue';
import AddUnit from '../views/AddUnit.vue';
import Payments from '../views/Payments.vue';
import Vendors from '../views/Vendors.vue';
import Settings from '../views/Settings.vue';
import { setupAuthGuards } from './guards';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: Login,
      meta: { requiresAuth: false },
    },
    {
      path: '/signup',
      name: 'Signup',
      component: Signup,
      meta: { requiresAuth: false },
    },
    {
      path: '/forgot-password',
      name: 'ForgotPassword',
      component: ForgotPassword,
      meta: { requiresAuth: false },
    },
    {
      path: '/reset-password',
      name: 'ResetPassword',
      component: ResetPassword,
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
      path: '/vendors',
      name: 'Vendors',
      component: Vendors,
      meta: { requiresAuth: true },
    },
    {
      path: '/settings',
      name: 'Settings',
      component: Settings,
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

// Setup authentication guards
setupAuthGuards(router);

export default router;
