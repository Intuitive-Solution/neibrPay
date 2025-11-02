<template>
  <div id="app">
    <!-- Use sidebar layout for authenticated pages -->
    <AppSidebar
      v-if="
        $route.name !== 'Login' &&
        $route.name !== 'Signup' &&
        $route.name !== 'ForgotPassword' &&
        $route.name !== 'ResetPassword' &&
        $route.name !== 'TermsOfService' &&
        $route.name !== 'PrivacyNotice' &&
        $route.name !== 'MagicLinkAuth'
      "
    >
      <router-view />
    </AppSidebar>

    <!-- Use simple layout for non-authenticated pages -->
    <div v-else class="min-h-screen">
      <router-view />
    </div>
  </div>
</template>

<script setup lang="ts">
import AppSidebar from './components/AppSidebar.vue';
</script>

<style>
#app {
  font-family: 'Inter', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
