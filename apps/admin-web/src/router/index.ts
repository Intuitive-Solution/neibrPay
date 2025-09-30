import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Login.vue';
import Signup from '../views/Signup.vue';
import TermsOfService from '../views/TermsOfService.vue';
import PrivacyNotice from '../views/PrivacyNotice.vue';
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
