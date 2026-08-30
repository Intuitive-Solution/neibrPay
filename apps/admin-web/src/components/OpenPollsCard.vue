<template>
  <div v-if="showCard" class="card-modern">
    <div class="flex items-center justify-between gap-4 mb-4">
      <h2 class="text-lg font-medium text-gray-900">Polls</h2>
      <div class="flex items-center gap-3">
        <router-link
          v-if="closedCount > 0"
          :to="closedPollsLink"
          class="text-sm font-medium text-primary hover:text-primary-600"
        >
          {{ closedCount }} closed poll{{ closedCount === 1 ? '' : 's' }}
        </router-link>
        <router-link
          :to="allPollsLink"
          class="text-sm font-medium text-gray-500 hover:text-gray-900"
        >
          View all
        </router-link>
      </div>
    </div>

    <ul v-if="listedPolls.length > 0" class="divide-y divide-gray-100">
      <li v-for="poll in listedPolls" :key="poll.id">
        <router-link
          :to="pollHref(poll)"
          class="flex items-center justify-between gap-4 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg"
        >
          <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">
              {{ poll.title }}
            </p>
            <p class="text-[13px] text-gray-500 mt-0.5">
              <template v-if="poll.status === PollStatus.DRAFT">
                Draft · not published
              </template>
              <template v-else>
                {{ poll.responded_unit_count }} of {{ poll.target_unit_count }}
                units voted
                <template v-if="!isAdmin && poll.can_vote">
                  · Your unit hasn't voted
                </template>
                <template v-else-if="!isAdmin && poll.has_voted">
                  · Your unit voted
                </template>
              </template>
            </p>
          </div>
          <span
            :class="[
              'badge flex-shrink-0',
              poll.status === PollStatus.OPEN
                ? 'badge-paid'
                : poll.status === PollStatus.DRAFT
                  ? 'badge-draft'
                  : 'bg-gray-100 text-gray-600',
            ]"
          >
            {{
              poll.status === PollStatus.OPEN
                ? 'Open'
                : poll.status === PollStatus.DRAFT
                  ? 'Draft'
                  : 'Closed'
            }}
          </span>
        </router-link>
      </li>
    </ul>

    <p v-else class="text-sm text-gray-500">
      No open polls right now.
      <router-link
        v-if="closedCount > 0"
        :to="closedPollsLink"
        class="font-medium text-primary hover:text-primary-600"
      >
        View closed poll results
      </router-link>
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { usePolls, useUserPolls } from '../composables/usePolls';
import { PollStatus } from '@neibrpay/models';

interface DashboardPoll {
  id: number;
  title: string;
  status: PollStatus;
  responded_unit_count: number;
  target_unit_count: number;
  can_vote?: boolean;
  has_voted?: boolean;
}

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.isAdmin);

const { data: userPolls } = useUserPolls();
const { data: adminList } = usePolls(
  computed(() => ({ status: 'all' as const })),
  { enabled: isAdmin }
);

const allPollsLink = computed(() => (isAdmin.value ? '/polls' : '/my-polls'));
const closedPollsLink = computed(() =>
  isAdmin.value ? '/polls?status=closed' : '/my-polls#past-polls'
);

const listedPolls = computed<DashboardPoll[]>(() => {
  if (isAdmin.value) {
    return (adminList.value?.data ?? [])
      .filter(
        poll =>
          poll.status === PollStatus.OPEN || poll.status === PollStatus.DRAFT
      )
      .slice(0, 5);
  }

  return (userPolls.value?.data ?? []).filter(
    poll => poll.status === PollStatus.OPEN
  );
});

const closedCount = computed(() => {
  if (isAdmin.value) {
    return (adminList.value?.data ?? []).filter(
      poll => poll.status === PollStatus.CLOSED
    ).length;
  }

  return (userPolls.value?.data ?? []).filter(
    poll => poll.status === PollStatus.CLOSED
  ).length;
});

const showCard = computed(
  () => listedPolls.value.length > 0 || closedCount.value > 0
);

function pollHref(poll: DashboardPoll): string {
  if (!isAdmin.value) {
    return `/my-polls#poll-${poll.id}`;
  }

  if (poll.status === PollStatus.DRAFT) {
    return `/polls/${poll.id}/edit`;
  }

  return `/polls/${poll.id}`;
}
</script>
