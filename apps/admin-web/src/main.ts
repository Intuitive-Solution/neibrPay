import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createPinia } from 'pinia';
import { useAuthStore } from './stores/auth';
import { VueQueryPlugin } from '@tanstack/vue-query';
import { queryClient } from './config/queryClient';
import { setAuthTokenGetter } from '@neibrpay/api-client';
import './style.css';

// Initialize Firebase
import './config/firebase';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(VueQueryPlugin, { queryClient });

// Initialize auth store after pinia is set up
const authStore = useAuthStore();
authStore.initializeAuth();

// Set up API client token getter
setAuthTokenGetter(() => authStore.token);

app.mount('#app');
