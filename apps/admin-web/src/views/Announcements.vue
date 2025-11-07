<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Announcements</h1>
        <p class="text-gray-600 mt-1">Manage community announcements</p>
      </div>
      <button
        v-if="authStore.isAdmin"
        @click="router.push('/announcements/create')"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
      >
        <svg
          class="w-5 h-5 mr-2"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
          />
        </svg>
        Add Announcement
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Active Announcements -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-green-500': statusFilter === 'active',
        }"
        @click="filterByStatus('active')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <svg
              class="w-6 h-6 text-green-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Active</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ activeAnnouncementsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- Expired Announcements -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-red-500': statusFilter === 'expired',
        }"
        @click="filterByStatus('expired')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-red-100 rounded-lg">
            <svg
              class="w-6 h-6 text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Expired</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ expiredAnnouncementsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- All Announcements -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-blue-500': statusFilter === 'all',
        }"
        @click="filterByStatus('all')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg
              class="w-6 h-6 text-blue-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">All</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ allAnnouncementsCount }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Announcements Table -->
    <div class="bg-white rounded-lg shadow-sm">
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-medium text-gray-900">Announcements</h2>
          <div class="flex items-center gap-3">
            <select
              v-model="statusFilter"
              class="input-field text-sm"
              @change="applyFilters"
            >
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="expired">Expired</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="p-8 text-center">
        <svg
          class="animate-spin h-8 w-8 text-primary mx-auto"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        <p class="mt-2 text-gray-600">Loading announcements...</p>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="filteredAnnouncements.length === 0"
        class="p-8 text-center"
      >
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No announcements</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by creating a new announcement.
        </p>
        <div v-if="authStore.isAdmin" class="mt-6">
          <button
            @click="router.push('/announcements/create')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
              />
            </svg>
            Add Announcement
          </button>
        </div>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Subject
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Recipients
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Created
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Removal Date
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="announcement in filteredAnnouncements"
              :key="announcement.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ announcement.subject }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-500">
                  {{ formatRecipientSummary(announcement.recipients || []) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">
                  {{ formatDate(announcement.created_at) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">
                  {{
                    announcement.removal_date
                      ? formatDate(announcement.removal_date)
                      : '—'
                  }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    isExpired(announcement)
                      ? 'bg-red-100 text-red-800'
                      : 'bg-green-100 text-green-800',
                  ]"
                >
                  {{ isExpired(announcement) ? 'Expired' : 'Active' }}
                </span>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex items-center justify-end gap-2">
                  <button
                    v-if="
                      authStore.isAdmin &&
                      announcement.created_by === authStore.user?.id
                    "
                    @click="
                      router.push(`/announcements/${announcement.id}/edit`)
                    "
                    class="text-primary hover:text-primary-600"
                  >
                    Edit
                  </button>
                  <button
                    v-if="authStore.isAdmin"
                    @click="handleDelete(announcement.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  useAnnouncements,
  useDeleteAnnouncement,
} from '../composables/useAnnouncements';
import { formatRecipientSummary } from '@neibrpay/models';
import type { Announcement } from '@neibrpay/models';

const router = useRouter();
const authStore = useAuthStore();

// Check admin access
if (!authStore.isAdmin) {
  router.push('/');
}

const statusFilter = ref<'active' | 'expired' | 'all'>('active');

// Filters
const filters = computed(() => ({
  status: statusFilter.value === 'all' ? undefined : statusFilter.value,
}));

const { data: announcements, isLoading, refetch } = useAnnouncements(filters);
const deleteMutation = useDeleteAnnouncement();

const filteredAnnouncements = computed(() => {
  if (!announcements.value) return [];
  return announcements.value.data || [];
});

const activeAnnouncementsCount = computed(() => {
  if (!announcements.value) return 0;
  return filteredAnnouncements.value.filter(a => !isExpired(a)).length;
});

const expiredAnnouncementsCount = computed(() => {
  if (!announcements.value) return 0;
  return filteredAnnouncements.value.filter(a => isExpired(a)).length;
});

const allAnnouncementsCount = computed(() => {
  return filteredAnnouncements.value.length;
});

function isExpired(announcement: Announcement): boolean {
  if (!announcement.removal_date) return false;
  const removalDate = new Date(announcement.removal_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return removalDate < today;
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function filterByStatus(status: 'active' | 'expired' | 'all') {
  statusFilter.value = status;
  applyFilters();
}

function applyFilters() {
  refetch();
}

async function handleDelete(id: number) {
  if (!confirm('Are you sure you want to delete this announcement?')) {
    return;
  }

  try {
    await deleteMutation.mutateAsync(id);
    refetch();
  } catch (error) {
    console.error('Failed to delete announcement:', error);
    alert('Failed to delete announcement. Please try again.');
  }
}
</script>
