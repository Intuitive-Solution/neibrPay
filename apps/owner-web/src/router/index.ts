import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'Dashboard',
      component: () => import('../views/Dashboard.vue'),
    },
    {
      path: '/budget',
      name: 'Budget',
      component: () => import('../views/Budget.vue'),
    },
  ],
});

export default router;
