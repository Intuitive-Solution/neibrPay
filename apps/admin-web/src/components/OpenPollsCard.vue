<template>
  <div v-if="showCard" class="card-modern">
    <div class="flex items-center justify-between gap-4 mb-4">
      <div class="flex items-center gap-3 min-w-0">
        <h2 class="text-lg font-medium text-gray-900">Polls</h2>
        <span
          v-if="votablePolls.length > 0"
          class="badge bg-primary-100 text-primary-800"
        >
          {{ votablePolls.length }} need{{
            votablePolls.length === 1 ? 's' : ''
          }}
          your vote
        </span>
      </div>
      <div class="flex items-center gap-3 flex-shrink-0">
        <router-link
          v-if="closedCount > 0"
          :to="closedPollsLink"
          class="text-sm font-medium text-primary hover:text-primary-600"
        >
          {{ closedCount }} closed poll{{ closedCount === 1 ? '' : 's' }}
        </router-link>
        <router-link
          to="/my-polls"
          class="text-sm font-medium text-primary hover:text-primary-600"
        >
          My Polls
        </router-link>
        <router-link
          v-if="isAdmin"
          to="/polls"
          class="text-sm font-medium text-gray-500 hover:text-gray-900"
        >
          Manage
        </router-link>
        <router-link
          v-else
          to="/my-polls"
          class="text-sm font-medium text-gray-500 hover:text-gray-900"
        >
          View all
        </router-link>
      </div>
    </div>

    <div class="space-y-4">
      <!-- Ballots needing a vote -->
      <PollVoteCard
        v-for="poll in votablePolls"
        :key="`vote-${poll.id}`"
        :poll="poll"
        variant="needs-vote"
        embedded
        :expanded="expandedVoteId === poll.id"
        @expand="expandedVoteId = poll.id"
      />

      <!-- Open polls already voted -->
      <div v-if="votedOpenPolls.length > 0" class="space-y-3">
        <p
          v-if="votablePolls.length > 0"
          class="text-[13px] font-medium text-gray-500"
        >
          Voted · awaiting results
        </p>
        <div class="divide-y divide-gray-100 -mx-1">
          <PollVoteCard
            v-for="poll in votedOpenPolls"
            :key="`voted-${poll.id}`"
            :poll="poll"
            variant="voted"
            embedded
          />
        </div>
      </div>

      <!-- Drafts / scheduled -->
      <ul
        v-if="summaryPolls.length > 0"
        class="divide-y divide-gray-100 border-t border-gray-100 pt-1"
      >
        <li v-for="poll in summaryPolls" :key="`summary-${poll.id}`">
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
                <template v-else-if="poll.opensLater">
                  Opens {{ poll.opensLabel }}
                </template>
                <template v-else>
                  {{ poll.responded_unit_count }} of
                  {{ poll.target_unit_count }} units voted
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
                  ? poll.opensLater
                    ? 'Scheduled'
                    : 'Open'
                  : poll.status === PollStatus.DRAFT
                    ? 'Draft'
                    : 'Closed'
              }}
            </span>
          </router-link>
        </li>
      </ul>

      <p
        v-if="
          votablePolls.length === 0 &&
          votedOpenPolls.length === 0 &&
          summaryPolls.length === 0
        "
        class="text-sm text-gray-500"
      >
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
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useAuthStore } from '../stores/auth';
import { usePolls, useUserPolls } from '../composables/usePolls';
import PollVoteCard from './PollVoteCard.vue';
import { PollStatus, type ResidentPoll } from '@neibrpay/models';

interface DashboardPoll {
  id: number;
  title: string;
  status: PollStatus;
  responded_unit_count: number;
  target_unit_count: number;
  can_vote?: boolean;
  has_voted?: boolean;
  opensLater?: boolean;
  opensLabel?: string;
}

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.isAdmin);

const { data: userPolls } = useUserPolls();
const { data: adminList } = usePolls(
  computed(() => ({ status: 'all' as const })),
  { enabled: isAdmin }
);

const expandedVoteId = ref<number | null>(null);

const residentPolls = computed(() => userPolls.value?.data ?? []);

const votablePolls = computed<ResidentPoll[]>(() =>
  residentPolls.value.filter(poll => poll.can_vote)
);

const votedOpenPolls = computed<ResidentPoll[]>(() =>
  residentPolls.value.filter(
    poll => poll.status === PollStatus.OPEN && poll.has_voted && !poll.can_vote
  )
);

watch(
  votablePolls,
  list => {
    if (list.length === 0) {
      expandedVoteId.value = null;
      return;
    }
    if (
      expandedVoteId.value === null ||
      !list.some(poll => poll.id === expandedVoteId.value)
    ) {
      expandedVoteId.value = list[0].id;
    }
  },
  { immediate: true }
);

const closedPollsLink = computed(() =>
  isAdmin.value ? '/polls?status=closed' : '/my-polls#past-polls'
);

function formatOpenDate(value: string): string {
  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

/** Drafts (admin) and published polls that are not yet open for this unit. */
const summaryPolls = computed<DashboardPoll[]>(() => {
  const shownIds = new Set([
    ...votablePolls.value.map(poll => poll.id),
    ...votedOpenPolls.value.map(poll => poll.id),
  ]);

  if (isAdmin.value) {
    return (adminList.value?.data ?? [])
      .filter(
        poll =>
          (poll.status === PollStatus.OPEN ||
            poll.status === PollStatus.DRAFT) &&
          !shownIds.has(poll.id)
      )
      .slice(0, 5)
      .map(poll => {
        const resident = residentPolls.value.find(item => item.id === poll.id);
        const opensLater = Boolean(
          poll.status === PollStatus.OPEN &&
            poll.opens_at &&
            new Date(poll.opens_at).getTime() > Date.now() &&
            !resident?.has_voted
        );
        return {
          id: poll.id,
          title: poll.title,
          status: poll.status,
          responded_unit_count: poll.responded_unit_count,
          target_unit_count: poll.target_unit_count,
          can_vote: resident?.can_vote,
          has_voted: resident?.has_voted,
          opensLater,
          opensLabel: poll.opens_at ? formatOpenDate(poll.opens_at) : undefined,
        };
      });
  }

  return residentPolls.value
    .filter(
      poll =>
        poll.status === PollStatus.OPEN &&
        !poll.can_vote &&
        !poll.has_voted &&
        !shownIds.has(poll.id)
    )
    .map(poll => {
      const opensLater = Boolean(
        poll.opens_at && new Date(poll.opens_at).getTime() > Date.now()
      );
      return {
        id: poll.id,
        title: poll.title,
        status: poll.status,
        responded_unit_count: poll.responded_unit_count,
        target_unit_count: poll.target_unit_count,
        can_vote: poll.can_vote,
        has_voted: poll.has_voted,
        opensLater,
        opensLabel: poll.opens_at ? formatOpenDate(poll.opens_at) : undefined,
      };
    });
});

const closedCount = computed(() => {
  if (isAdmin.value) {
    return (adminList.value?.data ?? []).filter(
      poll => poll.status === PollStatus.CLOSED
    ).length;
  }

  return residentPolls.value.filter(poll => poll.status === PollStatus.CLOSED)
    .length;
});

const showCard = computed(
  () =>
    votablePolls.value.length > 0 ||
    votedOpenPolls.value.length > 0 ||
    summaryPolls.value.length > 0 ||
    closedCount.value > 0
);

function pollHref(poll: DashboardPoll): string {
  if (poll.status === PollStatus.DRAFT) {
    return `/polls/${poll.id}/edit`;
  }

  if (isAdmin.value && !poll.can_vote && !poll.has_voted && !poll.opensLater) {
    return `/polls/${poll.id}`;
  }

  return `/my-polls?poll=${poll.id}`;
}
</script>
