import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createPinia } from 'pinia';
import { useAuthStore } from './stores/auth';
import { VueQueryPlugin } from '@tanstack/vue-query';
import { queryClient } from './config/queryClient';
import { setAuthTokenGetter } from '@neibrpay/api-client';
import { setupAuthGuards } from './router/guards';
import { usePostHog } from './composables/usePostHog';
import './style.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(VueQueryPlugin, { queryClient });

// Setup authentication guards AFTER Pinia is initialized
setupAuthGuards(router);

// Initialize auth store after pinia is set up
const authStore = useAuthStore();
authStore.initializeAuth();

// Set up API client token getter
setAuthTokenGetter(() => authStore.token);

// Initialize PostHog tracking
usePostHog();

app.mount('#app');
