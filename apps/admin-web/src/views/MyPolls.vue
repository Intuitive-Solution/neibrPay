<template>
  <div class="space-y-6">
    <div v-if="isLoading" class="card-modern text-center py-12">
      <p class="text-gray-500">Loading polls...</p>
    </div>

    <div
      v-else-if="error"
      class="p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <p class="text-sm text-red-800">We couldn't load your polls.</p>
    </div>

    <div
      v-else-if="polls.length === 0"
      class="card-modern flex flex-col items-center text-center py-16 px-6"
    >
      <div
        class="w-16 h-16 rounded-2xl bg-primary-50 text-primary flex items-center justify-center"
      >
        <svg
          class="w-8 h-8"
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
      <h3 class="mt-4 text-xl font-semibold text-gray-900">
        No polls for your unit
      </h3>
      <p class="mt-3 max-w-md text-[15px] leading-relaxed text-gray-600">
        When your board asks the community a question, it will show up here.
      </p>
    </div>

    <template v-else>
      <!-- Open polls -->
      <section v-if="openPolls.length > 0" class="space-y-4">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Open polls</h2>
          <p class="text-sm text-gray-600 mt-1">
            Your unit gets one vote on each
          </p>
        </div>
        <PollVoteCard v-for="poll in openPolls" :key="poll.id" :poll="poll" />
      </section>

      <!-- Past polls -->
      <section v-if="pastPolls.length > 0" class="space-y-4">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Past polls</h2>
          <p class="text-sm text-gray-600 mt-1">
            Results your board has shared with the community
          </p>
        </div>
        <PollVoteCard v-for="poll in pastPolls" :key="poll.id" :poll="poll" />
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useUserPolls } from '../composables/usePolls';
import PollVoteCard from '../components/PollVoteCard.vue';
import { PollStatus } from '@neibrpay/models';

const { data, isLoading, error } = useUserPolls();

const polls = computed(() => data.value?.data ?? []);

// A poll stays in the top section while it's open, whether or not we voted
const openPolls = computed(() =>
  polls.value.filter(poll => poll.status === PollStatus.OPEN)
);

const pastPolls = computed(() =>
  polls.value.filter(poll => poll.status === PollStatus.CLOSED)
);
</script>
