<template>
  <div class="space-y-8">
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
        {{ isAdmin ? 'Nothing to vote on' : 'No polls for your unit' }}
      </h3>
      <p class="mt-3 max-w-md text-[15px] leading-relaxed text-gray-600">
        {{
          isAdmin
            ? 'Polls to vote on appear here when you own a unit that is included. Create and manage community polls from Polls. Unpublished drafts are not shown to residents.'
            : 'When your board publishes a poll for your unit, it will show up here.'
        }}
      </p>
      <router-link v-if="isAdmin" to="/polls" class="btn-primary mt-6">
        Go to Polls
      </router-link>
    </div>

    <template v-else>
      <!-- Filters + tip -->
      <div
        class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
      >
        <div
          class="inline-flex flex-wrap gap-2 p-1 bg-gray-100 rounded-xl w-fit"
        >
          <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            @click="selectTab(tab.value)"
            :class="[
              'inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm transition-colors',
              activeTab === tab.value
                ? 'bg-white shadow-sm font-semibold text-gray-900'
                : 'font-medium text-gray-600 hover:text-gray-900',
            ]"
          >
            {{ tab.label }}
            <span
              :class="[
                'min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-semibold inline-flex items-center justify-center',
                activeTab === tab.value
                  ? 'bg-primary text-white'
                  : 'bg-gray-200 text-gray-700',
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>

        <p
          class="flex items-start gap-2 text-[13px] text-gray-500 max-w-sm lg:justify-end"
        >
          <svg
            class="w-4 h-4 text-primary flex-shrink-0 mt-0.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 13l4 4L19 7"
            />
          </svg>
          <span
            >Your unit has one vote — first owner to submit records it.</span
          >
        </p>
      </div>

      <!-- Needs your vote -->
      <section
        v-if="needsVotePolls.length > 0"
        id="needs-vote"
        class="space-y-4 scroll-mt-24"
      >
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Needs your vote</h2>
        </div>

        <PollVoteCard
          v-for="poll in needsVotePolls"
          :key="poll.id"
          :poll="poll"
          variant="needs-vote"
          :expanded="expandedVoteId === poll.id"
          @expand="expandVote(poll.id)"
        />
      </section>

      <!-- Voted · awaiting results -->
      <section
        v-if="votedAwaitingPolls.length > 0"
        id="voted-polls"
        class="space-y-4 scroll-mt-24"
      >
        <div>
          <h2 class="text-xl font-semibold text-gray-900">
            Voted · awaiting results
          </h2>
          <p class="text-sm text-gray-600 mt-1">Nothing more to do here.</p>
        </div>

        <PollVoteCard
          v-for="poll in votedAwaitingPolls"
          :key="poll.id"
          :poll="poll"
          variant="voted"
        />
      </section>

      <!-- Closed polls -->
      <section
        v-if="closedPolls.length > 0"
        id="past-polls"
        class="space-y-4 scroll-mt-24"
      >
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Closed polls</h2>
          <p class="text-sm text-gray-600 mt-1">
            Results your board has shared with the community
          </p>
        </div>

        <PollVoteCard
          v-for="poll in closedPolls"
          :key="poll.id"
          :poll="poll"
          variant="closed"
          :expanded="expandedClosedId === poll.id"
          @expand="toggleClosed(poll.id)"
        />
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useUserPolls } from '../composables/usePolls';
import PollVoteCard from '../components/PollVoteCard.vue';
import { PollStatus } from '@neibrpay/models';

type FilterTab = 'needs' | 'voted' | 'closed';

const route = useRoute();
const authStore = useAuthStore();
const isAdmin = computed(() => authStore.isAdmin);

const { data, isLoading, error } = useUserPolls();

const polls = computed(() => data.value?.data ?? []);

const needsVotePolls = computed(() =>
  polls.value.filter(poll => poll.can_vote)
);

const votedAwaitingPolls = computed(() =>
  polls.value.filter(
    poll => poll.status === PollStatus.OPEN && poll.has_voted && !poll.can_vote
  )
);

const closedPolls = computed(() =>
  polls.value.filter(poll => poll.status === PollStatus.CLOSED)
);

const activeTab = ref<FilterTab>('needs');
const expandedVoteId = ref<number | null>(null);
const expandedClosedId = ref<number | null>(null);
const didInit = ref(false);

const tabs = computed(() => [
  {
    value: 'needs' as const,
    label: 'Needs your vote',
    count: needsVotePolls.value.length,
    sectionId: 'needs-vote',
  },
  {
    value: 'voted' as const,
    label: 'Voted',
    count: votedAwaitingPolls.value.length,
    sectionId: 'voted-polls',
  },
  {
    value: 'closed' as const,
    label: 'Closed',
    count: closedPolls.value.length,
    sectionId: 'past-polls',
  },
]);

function selectTab(tab: FilterTab) {
  activeTab.value = tab;
  const sectionId = tabs.value.find(item => item.value === tab)?.sectionId;
  if (!sectionId) return;
  nextTick(() => {
    document.getElementById(sectionId)?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  });
}

function expandVote(id: number) {
  expandedVoteId.value = id;
  activeTab.value = 'needs';
}

function toggleClosed(id: number) {
  expandedClosedId.value = expandedClosedId.value === id ? null : id;
  activeTab.value = 'closed';
}

function deepLinkPollId(): number | null {
  const pollQuery = route.query.poll;
  const raw = Array.isArray(pollQuery) ? pollQuery[0] : pollQuery;
  if (raw) {
    const id = Number(raw);
    return Number.isFinite(id) ? id : null;
  }

  if (
    typeof window !== 'undefined' &&
    window.location.hash.startsWith('#poll-')
  ) {
    const id = Number(window.location.hash.replace('#poll-', ''));
    return Number.isFinite(id) ? id : null;
  }

  return null;
}

watch(
  [isLoading, polls],
  ([loading]) => {
    if (loading || didInit.value) {
      return;
    }

    const linkedId = deepLinkPollId();
    if (linkedId) {
      const linked = polls.value.find(poll => poll.id === linkedId);
      if (linked?.can_vote) {
        activeTab.value = 'needs';
        expandedVoteId.value = linkedId;
      } else if (linked?.status === PollStatus.OPEN && linked.has_voted) {
        activeTab.value = 'voted';
      } else if (linked?.status === PollStatus.CLOSED) {
        activeTab.value = 'closed';
        expandedClosedId.value = linkedId;
      }
    } else {
      if (needsVotePolls.value.length > 0) {
        activeTab.value = 'needs';
        expandedVoteId.value = needsVotePolls.value[0]?.id ?? null;
      } else if (votedAwaitingPolls.value.length > 0) {
        activeTab.value = 'voted';
      } else {
        activeTab.value = 'closed';
        expandedClosedId.value = closedPolls.value[0]?.id ?? null;
      }
    }

    didInit.value = true;
  },
  { immediate: true }
);

watch(needsVotePolls, list => {
  if (list.length > 0 && expandedVoteId.value === null) {
    expandedVoteId.value = list[0].id;
  }
  if (
    expandedVoteId.value !== null &&
    !list.some(poll => poll.id === expandedVoteId.value)
  ) {
    expandedVoteId.value = list[0]?.id ?? null;
  }
});

watch(
  [isLoading, polls, () => route.query.poll, didInit],
  async ([loading, , , initialized]) => {
    if (loading || !initialized || typeof window === 'undefined') {
      return;
    }

    const linkedId = deepLinkPollId();
    if (!linkedId) {
      return;
    }

    await nextTick();
    document.querySelector(`#poll-${linkedId}`)?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  },
  { immediate: true }
);
</script>
