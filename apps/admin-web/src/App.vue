<template>
  <div id="app">
    <!-- Header only shown when not on login page -->
    <header v-if="$route.name !== 'Login'" class="text-white p-4 bg-primary">
      <h1 class="text-2xl font-bold">NeibrPay - HOA Manager Dashboard</h1>
    </header>

    <!-- Main content with conditional styling -->
    <main :class="$route.name === 'Login' ? '' : 'container mx-auto p-4'">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router';

const $route = useRoute();
</script>

<style>
#app {
  font-family: 'Inter', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
