import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Login.vue';
import Signup from '../views/Signup.vue';
import TermsOfService from '../views/TermsOfService.vue';
import PrivacyNotice from '../views/PrivacyNotice.vue';
import Invoices from '../views/Invoices.vue';
import People from '../views/People.vue';
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
      path: '/people',
      name: 'People',
      component: People,
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
