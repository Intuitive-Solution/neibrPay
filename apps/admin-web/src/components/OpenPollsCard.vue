<template>
  <!-- Ballots the current user's unit can still cast -->
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

  <!-- Board: open and draft polls they are running (when they have nothing to vote on) -->
  <div v-else-if="isAdmin && managedPolls.length > 0" class="card-modern">
    <div class="flex items-center justify-between gap-4 mb-4">
      <h2 class="text-lg font-medium text-gray-900">Polls</h2>
      <router-link
        to="/polls"
        class="text-sm font-medium text-primary hover:text-primary-600"
      >
        View all
      </router-link>
    </div>

    <ul class="divide-y divide-gray-100">
      <li v-for="poll in managedPolls" :key="poll.id">
        <router-link
          :to="
            poll.status === 'draft'
              ? `/polls/${poll.id}/edit`
              : `/polls/${poll.id}`
          "
          class="flex items-center justify-between gap-4 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg"
        >
          <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">
              {{ poll.title }}
            </p>
            <p class="text-[13px] text-gray-500 mt-0.5">
              <template v-if="poll.status === 'draft'"
                >Draft · not published</template
              >
              <template v-else>
                {{ poll.responded_unit_count }} of {{ poll.target_unit_count }}
                units voted
              </template>
            </p>
          </div>
          <span
            :class="[
              'badge flex-shrink-0',
              poll.status === 'open' ? 'badge-paid' : 'badge-draft',
            ]"
          >
            {{ poll.status === 'open' ? 'Open' : 'Draft' }}
          </span>
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { usePolls, useUserPolls } from '../composables/usePolls';
import PollVoteCard from './PollVoteCard.vue';
import { PollStatus } from '@neibrpay/models';

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.isAdmin);

const { data } = useUserPolls();
const { data: adminList } = usePolls(
  computed(() => ({ status: 'all' as const })),
  { enabled: isAdmin }
);

const votablePolls = computed(() =>
  (data.value?.data ?? []).filter(poll => poll.can_vote)
);

const managedPolls = computed(() =>
  (adminList.value?.data ?? [])
    .filter(
      poll =>
        poll.status === PollStatus.OPEN || poll.status === PollStatus.DRAFT
    )
    .slice(0, 5)
);
</script>
