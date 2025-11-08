<template>
  <div v-if="announcements && announcements.length > 0" class="card">
    <div
      class="px-6 py-4 border-b border-gray-200 flex items-center justify-between"
    >
      <h2 class="text-lg font-medium text-gray-900">Announcements</h2>
      <div v-if="announcements.length > 1" class="flex items-center gap-2">
        <button
          @click="previousSlide"
          :disabled="currentIndex === 0"
          class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            class="w-5 h-5 text-gray-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"
            />
          </svg>
        </button>
        <span class="text-sm text-gray-500">
          {{ currentIndex + 1 }} / {{ announcements.length }}
        </span>
        <button
          @click="nextSlide"
          :disabled="currentIndex === announcements.length - 1"
          class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            class="w-5 h-5 text-gray-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"
            />
          </svg>
        </button>
      </div>
    </div>

    <div class="p-6">
      <!-- Single Announcement View -->
      <div v-if="announcements.length === 1" class="space-y-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            {{ currentAnnouncement.subject }}
          </h3>
          <div
            class="text-sm text-gray-700 prose prose-sm max-w-none"
            v-html="currentAnnouncement.message"
          ></div>
        </div>
        <div class="text-xs text-gray-500">
          {{ formatDate(currentAnnouncement.created_at) }}
        </div>
      </div>

      <!-- Carousel View -->
      <div v-else class="relative">
        <div class="overflow-hidden">
          <div
            class="flex transition-transform duration-300 ease-in-out"
            :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
          >
            <div
              v-for="(announcement, index) in announcements"
              :key="announcement.id"
              class="min-w-full px-2"
            >
              <div class="space-y-4">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ announcement.subject }}
                  </h3>
                  <div
                    class="text-sm text-gray-700 prose prose-sm max-w-none"
                    v-html="announcement.message"
                  ></div>
                </div>
                <div class="text-xs text-gray-500">
                  {{ formatDate(announcement.created_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dot Indicators -->
        <div
          v-if="announcements.length > 1"
          class="flex justify-center gap-2 mt-6"
        >
          <button
            v-for="(announcement, index) in announcements"
            :key="announcement.id"
            @click="currentIndex = index"
            :class="[
              'w-2 h-2 rounded-full transition-colors',
              currentIndex === index ? 'bg-primary' : 'bg-gray-300',
            ]"
            :aria-label="`Go to announcement ${index + 1}`"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useUserAnnouncements } from '../composables/useAnnouncements';
import type { Announcement } from '@neibrpay/models';

const { data: announcementsData, isLoading } = useUserAnnouncements();

const announcements = computed(() => {
  if (!announcementsData.value) return [];
  return announcementsData.value.data || [];
});

const currentIndex = ref(0);

const currentAnnouncement = computed(() => {
  return announcements.value[currentIndex.value];
});

function nextSlide() {
  if (currentIndex.value < announcements.value.length - 1) {
    currentIndex.value++;
  }
}

function previousSlide() {
  if (currentIndex.value > 0) {
    currentIndex.value--;
  }
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}
</script>

<style scoped>
:deep(.prose) {
  color: inherit;
}

:deep(.prose p) {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}

:deep(.prose ul),
:deep(.prose ol) {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  padding-left: 1.5rem;
}

:deep(.prose li) {
  margin-top: 0.25rem;
  margin-bottom: 0.25rem;
}

:deep(.prose h1),
:deep(.prose h2),
:deep(.prose h3) {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}
</style>
