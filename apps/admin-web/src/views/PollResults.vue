<template>
  <div class="space-y-5">
    <div v-if="isLoading" class="card-modern text-center py-12">
      <p class="text-gray-500">Loading poll...</p>
    </div>

    <div
      v-else-if="error || !poll"
      class="p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <p class="text-sm text-red-800">We couldn't load this poll.</p>
    </div>

    <template v-else>
      <!-- Header -->
      <div class="card-modern">
        <div
          class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-xl font-semibold text-gray-900">
                {{ poll.title }}
              </h1>
              <span :class="['badge', statusBadgeClass]">
                <span
                  v-if="poll.status === 'open'"
                  class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5"
                ></span>
                {{ getPollStatusLabel(poll.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <router-link
              v-if="poll.status !== 'closed'"
              :to="`/polls/${poll.id}/edit`"
            >
              <button class="btn-outline btn-sm">Edit</button>
            </router-link>
            <button
              v-if="poll.status === 'draft'"
              class="btn-primary btn-sm"
              :disabled="publishPoll.isPending.value"
              @click="showPublishConfirm = true"
            >
              Publish
            </button>
            <button
              v-if="poll.status === 'open'"
              class="btn-outline btn-sm"
              :disabled="closePoll.isPending.value"
              @click="showCloseConfirm = true"
            >
              Close now
            </button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[1.35fr_1fr] gap-5 items-start">
        <!-- Tally, one card per question -->
        <div class="space-y-5">
          <div
            v-for="(question, index) in questionResults"
            :key="question.question_id"
            class="card-modern"
          >
            <div class="flex items-baseline justify-between gap-3">
              <div
                class="text-xs font-semibold uppercase tracking-wide text-gray-400"
              >
                {{
                  questionResults.length > 1
                    ? `Question ${index + 1} of ${questionResults.length}`
                    : 'The question'
                }}
              </div>
              <span class="text-[13px] text-gray-500 whitespace-nowrap">
                {{ getQuestionTypeLabel(question.type) }}
              </span>
            </div>
            <h2
              class="mt-2 text-[17px] font-semibold leading-snug text-gray-900"
            >
              {{ question.prompt }}
            </h2>

            <p
              v-if="question.total_votes === 0"
              class="mt-5 text-sm text-gray-500"
            >
              No votes yet. Results will appear here as units vote.
            </p>

            <div v-else class="mt-5 space-y-4">
              <div
                v-for="(option, optionIndex) in question.options"
                :key="option.id"
                class="space-y-2"
              >
                <div class="flex items-baseline justify-between gap-3">
                  <span
                    :class="[
                      'text-[15px] text-gray-900',
                      optionIndex === leadingIndexFor(question)
                        ? 'font-semibold'
                        : 'font-medium',
                    ]"
                  >
                    {{ option.label }}
                  </span>
                  <span
                    :class="[
                      'text-sm font-semibold whitespace-nowrap',
                      optionIndex === leadingIndexFor(question)
                        ? 'text-primary-700'
                        : 'text-gray-900',
                    ]"
                  >
                    {{ option.percentage }}% · {{ option.votes }}
                  </span>
                </div>
                <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
                  <div
                    :class="[
                      'h-full rounded-full',
                      optionIndex === leadingIndexFor(question)
                        ? 'bg-primary'
                        : 'bg-gray-300',
                    ]"
                    :style="{ width: option.percentage + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="flex gap-3 p-3.5 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-900"
          >
            <svg
              class="w-5 h-5 flex-shrink-0 text-blue-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <p>
              Tallies and the roster are queried separately — no view links a
              unit to its answers.
            </p>
          </div>
        </div>

        <!-- Participation roster -->
        <div class="card-modern">
          <div class="flex items-baseline justify-between gap-3">
            <h2 class="text-[17px] font-semibold text-gray-900">
              Participation
            </h2>
            <span class="text-[13px] font-semibold text-primary-700">
              {{ poll.responded_unit_count }} / {{ poll.target_unit_count }}
            </span>
          </div>

          <div class="h-2 rounded-full bg-gray-200 overflow-hidden my-3">
            <div
              class="h-full rounded-full bg-primary"
              :style="{ width: participationPercentage(poll) + '%' }"
            ></div>
          </div>

          <div class="inline-flex gap-1.5 p-1 bg-gray-100 rounded-lg mb-3">
            <button
              type="button"
              @click="rosterFilter = 'all'"
              :class="rosterTabClass('all')"
            >
              All {{ participation.length }}
            </button>
            <button
              type="button"
              @click="rosterFilter = 'pending'"
              :class="rosterTabClass('pending')"
            >
              Not yet ({{ pendingCount }})
            </button>
          </div>

          <p
            v-if="visibleParticipation.length === 0"
            class="py-6 text-sm text-center text-gray-500"
          >
            {{
              rosterFilter === 'pending'
                ? 'Every unit has voted.'
                : 'No units are targeted by this poll.'
            }}
          </p>

          <div v-else class="max-h-[420px] overflow-y-auto">
            <div
              v-for="row in visibleParticipation"
              :key="row.unit_id"
              class="flex items-center justify-between gap-3 py-2.5 border-b border-gray-100 last:border-0"
            >
              <span class="text-sm text-gray-900">{{ rosterLabel(row) }}</span>
              <span
                :class="[
                  'badge',
                  row.has_voted ? 'badge-paid' : 'badge-partial',
                ]"
              >
                {{ row.has_voted ? 'Voted' : 'Not yet' }}
              </span>
            </div>
          </div>

          <button
            v-if="poll.status === 'open' && pendingCount > 0"
            type="button"
            class="btn-primary btn-sm w-full mt-4"
            :disabled="remind.isPending.value"
            @click="showRemindConfirm = true"
          >
            {{
              remind.isPending.value
                ? 'Sending...'
                : `Remind the ${pendingCount} who haven't voted`
            }}
          </button>
          <p v-if="reminderMessage" class="mt-2 text-sm text-primary-700">
            {{ reminderMessage }}
          </p>
        </div>
      </div>
    </template>

    <PollPublishConfirmModal
      :is-open="showPublishConfirm"
      :is-submitting="publishPoll.isPending.value"
      @cancel="showPublishConfirm = false"
      @confirm="confirmPublish"
    />

    <PollRemindConfirmModal
      :is-open="showRemindConfirm"
      :pending-count="pendingCount"
      :is-submitting="remind.isPending.value"
      @cancel="showRemindConfirm = false"
      @confirm="confirmRemind"
    />

    <PollCloseConfirmModal
      :is-open="showCloseConfirm"
      :emails-results="emailsResultsOnClose"
      :is-submitting="closePoll.isPending.value"
      @cancel="showCloseConfirm = false"
      @confirm="confirmClose"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  usePoll,
  useClosePoll,
  usePublishPoll,
  useRemindPollNonVoters,
} from '../composables/usePolls';
import PollPublishConfirmModal from '../components/PollPublishConfirmModal.vue';
import PollRemindConfirmModal from '../components/PollRemindConfirmModal.vue';
import PollCloseConfirmModal from '../components/PollCloseConfirmModal.vue';
import {
  formatAudienceSummary,
  formatQuestionSummary,
  getPollStatusLabel,
  getQuestionTypeLabel,
  participationPercentage,
  PollResultsVisibility,
  PollStatus,
  type PollParticipant,
  type PollQuestionResults,
} from '@neibrpay/models';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

if (!authStore.isAdmin) {
  router.push('/');
}

const pollId = computed(() => parseInt(route.params.id as string));
const rosterFilter = ref<'all' | 'pending'>('all');
const showPublishConfirm = ref(false);
const showRemindConfirm = ref(false);
const showCloseConfirm = ref(false);
const reminderMessage = ref('');

const { data: poll, isLoading, error } = usePoll(pollId);
const publishPoll = usePublishPoll();
const closePoll = useClosePoll();
const remind = useRemindPollNonVoters();

const questionResults = computed<PollQuestionResults[]>(
  () => poll.value?.results?.questions ?? []
);
const participation = computed<PollParticipant[]>(
  () => poll.value?.participation ?? []
);

const pendingCount = computed(
  () => participation.value.filter(row => !row.has_voted).length
);

const emailsResultsOnClose = computed(
  () => poll.value?.results_visibility !== PollResultsVisibility.ADMINS_ONLY
);

const visibleParticipation = computed(() =>
  rosterFilter.value === 'pending'
    ? participation.value.filter(row => !row.has_voted)
    : participation.value
);

const statusBadgeClass = computed(() => {
  switch (poll.value?.status) {
    case PollStatus.OPEN:
      return 'badge-paid';
    case PollStatus.DRAFT:
      return 'badge-draft';
    default:
      return 'bg-gray-100 text-gray-600';
  }
});

const subtitle = computed(() => {
  if (!poll.value) return '';

  const parts: string[] = [];

  if (poll.value.status === PollStatus.CLOSED && poll.value.closes_at) {
    parts.push(`Closed ${formatDate(poll.value.closes_at)}`);
  } else if (poll.value.closes_at) {
    parts.push(`Closes ${formatDate(poll.value.closes_at)}`);
  }

  parts.push(formatQuestionSummary(poll.value.questions).toLowerCase());
  parts.push(formatAudienceSummary(poll.value));

  return parts.join(' · ');
});

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

/**
 * Index of the option with the most votes; -1 on a tie for first, so a tied
 * question never highlights an arbitrary leader.
 */
function leadingIndexFor(question: PollQuestionResults): number {
  if (question.total_votes === 0) {
    return -1;
  }

  let best = 0;
  question.options.forEach((option, index) => {
    if (option.votes > question.options[best].votes) {
      best = index;
    }
  });

  const tied = question.options.filter(
    option => option.votes === question.options[best].votes
  );

  return tied.length > 1 ? -1 : best;
}

function rosterTabClass(value: 'all' | 'pending'): string[] {
  return [
    'px-3 py-1 rounded-md text-[13px] transition-colors duration-150',
    rosterFilter.value === value
      ? 'bg-white shadow-sm font-semibold text-gray-900'
      : 'font-medium text-gray-600 hover:text-gray-900',
  ];
}

function rosterLabel(row: PollParticipant): string {
  const owners = row.owner_names.join(', ');
  return owners ? `${row.unit_title} · ${owners}` : row.unit_title;
}

function confirmPublish() {
  showPublishConfirm.value = false;
  publishPoll.mutate(pollId.value);
}

function confirmRemind() {
  remind.mutate(pollId.value, {
    onSuccess: result => {
      showRemindConfirm.value = false;
      reminderMessage.value = result.message;
    },
    onError: () => {
      showRemindConfirm.value = false;
    },
  });
}

function confirmClose() {
  closePoll.mutate(pollId.value, {
    onSettled: () => {
      showCloseConfirm.value = false;
    },
  });
}
</script>
