<template>
  <div class="dashboard">
    <!-- Dashboard content -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Financial Overview</h3>
        <p class="text-gray-600">Quick financial summary will go here</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Quick Actions</h3>
        <p class="text-gray-600">Common actions will go here</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Recent Activity</h3>
        <p class="text-gray-600">Recent activity feed will go here</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Dashboard component - logout functionality is handled by AppSidebar
</script>
