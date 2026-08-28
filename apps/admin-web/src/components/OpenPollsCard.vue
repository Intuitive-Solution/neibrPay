<template>
  <div v-if="votablePolls.length > 0" class="space-y-4">
    <div class="flex items-center justify-between gap-4">
      <h2 class="text-lg font-medium text-gray-900">Polls</h2>
      <span class="badge bg-primary-100 text-primary-800">
        {{ votablePolls.length }} open poll{{
          votablePolls.length === 1 ? '' : 's'
        }}
      </span>
    </div>

    <PollVoteCard v-for="poll in votablePolls" :key="poll.id" :poll="poll" />

    <router-link
      to="/my-polls"
      class="inline-block text-sm font-medium text-primary hover:text-primary-600"
    >
      View past polls
    </router-link>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useUserPolls } from '../composables/usePolls';
import PollVoteCard from '../components/PollVoteCard.vue';

const { data } = useUserPolls();

// The dashboard only surfaces polls the unit can still act on
const votablePolls = computed(() =>
  (data.value?.data ?? []).filter(poll => poll.can_vote)
);
</script>
