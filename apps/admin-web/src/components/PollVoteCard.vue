<template>
  <div
    :class="[
      'bg-white rounded-xl shadow-sm overflow-hidden',
      poll.can_vote ? 'border border-primary/35' : 'border border-gray-100',
    ]"
  >
    <!-- Open banner -->
    <div
      v-if="poll.can_vote"
      class="flex items-center justify-between gap-4 px-5 py-4 bg-primary-50 border-b border-primary/20"
    >
      <div class="flex items-center gap-3">
        <svg
          class="w-[22px] h-[22px] text-primary-600 flex-shrink-0"
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
        <div>
          <div class="text-base font-semibold text-primary-800">
            Your unit hasn't voted yet
          </div>
          <div v-if="closingLabel" class="text-[13px] text-primary-700">
            {{ closingLabel }}
          </div>
        </div>
      </div>
      <span
        v-if="poll.questions.length > 1"
        class="badge bg-primary-100 text-primary-800 whitespace-nowrap"
      >
        {{ poll.questions.length }} questions
      </span>
    </div>

    <!-- Voting form -->
    <div v-if="poll.can_vote" class="p-5 sm:p-6 space-y-5">
      <div>
        <h3 class="text-lg font-semibold text-gray-900">{{ poll.title }}</h3>
        <p
          v-if="poll.description"
          class="mt-1.5 max-w-2xl text-sm leading-relaxed text-gray-600"
        >
          {{ poll.description }}
        </p>
      </div>

      <div
        v-for="(question, questionIndex) in poll.questions"
        :key="question.id"
        class="space-y-3"
      >
        <div class="flex items-baseline gap-2">
          <span
            v-if="poll.questions.length > 1"
            class="text-[15px] font-semibold text-gray-400"
          >
            {{ questionIndex + 1 }}.
          </span>
          <p class="text-[15px] font-semibold text-gray-900">
            {{ question.prompt }}
            <span
              v-if="allowsMultipleAnswers(question.type)"
              class="ml-1 text-[13px] font-normal text-gray-500"
            >
              (choose all that apply)
            </span>
          </p>
        </div>

        <div class="space-y-2.5 max-w-2xl">
          <button
            v-for="option in question.options"
            :key="option.id"
            type="button"
            @click="selectOption(question, option.id)"
            :class="[
              'w-full flex items-center gap-3 min-h-[56px] px-4 py-3.5 rounded-lg text-left transition-colors',
              isSelected(question.id, option.id)
                ? 'border-2 border-primary bg-primary-50'
                : 'border border-gray-200 bg-white hover:bg-gray-50',
            ]"
          >
            <span
              :class="[
                'w-5 h-5 box-border flex-shrink-0',
                allowsMultipleAnswers(question.type)
                  ? 'rounded'
                  : 'rounded-full',
                isSelected(question.id, option.id)
                  ? 'border-[6px] border-primary bg-white'
                  : 'border border-gray-300',
              ]"
            ></span>
            <span
              :class="[
                'text-[15px] leading-snug text-gray-900',
                isSelected(question.id, option.id) ? 'font-semibold' : '',
              ]"
            >
              {{ option.label }}
            </span>
          </button>
        </div>
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex flex-wrap items-center gap-4">
        <button
          type="button"
          class="btn-primary btn-lg"
          :disabled="!allAnswered || castVote.isPending.value"
          @click="submitVote"
        >
          {{ castVote.isPending.value ? 'Submitting...' : 'Submit vote' }}
        </button>
        <span class="text-[13px] text-gray-600 max-w-md">
          One vote per unit — whoever on your unit votes first records it. Your
          answers are anonymous to the board.
        </span>
      </div>
    </div>

    <!-- Voted confirmation -->
    <div v-else-if="poll.has_voted" class="p-5 sm:p-6 space-y-4">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center flex-shrink-0"
        >
          <svg
            class="w-[22px] h-[22px]"
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
        </div>
        <div>
          <div class="text-[17px] font-semibold text-gray-900">
            Your unit's vote is recorded
          </div>
          <div class="text-[13px] text-gray-600">{{ votedByLabel }}</div>
        </div>
      </div>

      <div class="h-px bg-gray-100"></div>

      <div>
        <div
          class="text-xs font-semibold uppercase tracking-wide text-gray-400"
        >
          {{ poll.title }}
        </div>
        <p class="mt-1.5 text-[15px] text-gray-900">
          <template v-if="poll.results_visible">
            Results are in — see the tally below.
          </template>
          <template v-else-if="poll.closes_at">
            Results are shared with the community when the poll closes on
            <strong>{{ formatDate(poll.closes_at) }}</strong
            >.
          </template>
          <template v-else>
            Results are shared with the community when the poll closes.
          </template>
        </p>
      </div>

      <PollResultBars
        v-if="poll.results_visible"
        :results="poll.results"
        show-prompt
      />

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
          The board can see that your unit voted, but not which options you
          picked.
        </p>
      </div>
    </div>

    <!-- Closed without a vote from this unit -->
    <div v-else class="p-5 sm:p-6 space-y-4">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="text-[17px] font-semibold text-gray-900">
            {{ poll.title }}
          </h3>
          <p class="mt-1 text-[13px] text-gray-600">{{ closedSummary }}</p>
        </div>
        <span class="badge bg-gray-100 text-gray-600">Closed</span>
      </div>

      <PollResultBars
        v-if="poll.results_visible"
        :results="poll.results"
        show-prompt
      />
      <p v-else class="text-sm text-gray-500">
        The board hasn't shared the results of this poll.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useCastVote } from '../composables/usePolls';
import PollResultBars from './PollResultBars.vue';
import {
  allowsMultipleAnswers,
  type PollQuestion,
  type ResidentPoll,
} from '@neibrpay/models';

const props = defineProps<{ poll: ResidentPoll }>();

// question id => selected option ids
const selections = ref<Record<number, number[]>>({});
const errorMessage = ref('');
const castVote = useCastVote();

const allAnswered = computed(() =>
  props.poll.questions.every(
    question => (selections.value[question.id] ?? []).length > 0
  )
);

const closingLabel = computed(() => {
  if (!props.poll.closes_at) {
    return 'Open until the board closes it';
  }

  const closes = new Date(props.poll.closes_at);
  const days = Math.ceil((closes.getTime() - Date.now()) / 86_400_000);
  const closesOn = `Closes ${formatDate(props.poll.closes_at)}`;

  if (days <= 0) {
    return `${closesOn} · closing today`;
  }

  return `${closesOn} · ${days} day${days === 1 ? '' : 's'} left`;
});

const votedByLabel = computed(() => {
  const when = props.poll.voted_at ? formatDateTime(props.poll.voted_at) : null;

  if (props.poll.voted_by_me) {
    return when ? `Submitted by you on ${when}` : 'Submitted by you';
  }

  const who = props.poll.voted_by_name ?? 'a co-owner';
  return when
    ? `Already voted by your unit — submitted by ${who} on ${when}`
    : `Already voted by your unit — submitted by ${who}`;
});

const closedSummary = computed(() => {
  const parts: string[] = [];

  if (props.poll.closes_at) {
    parts.push(`Closed ${formatDate(props.poll.closes_at)}`);
  }

  parts.push(
    `${props.poll.responded_unit_count} of ${props.poll.target_unit_count} units voted`
  );

  return parts.join(' · ');
});

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function formatDateTime(value: string): string {
  return new Date(value).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

function isSelected(questionId: number, optionId: number): boolean {
  return (selections.value[questionId] ?? []).includes(optionId);
}

function selectOption(question: PollQuestion, optionId: number) {
  errorMessage.value = '';

  if (!allowsMultipleAnswers(question.type)) {
    selections.value[question.id] = [optionId];
    return;
  }

  const current = selections.value[question.id] ?? [];
  selections.value[question.id] = current.includes(optionId)
    ? current.filter(id => id !== optionId)
    : [...current, optionId];
}

function submitVote() {
  if (!allAnswered.value) {
    errorMessage.value = 'Please answer every question before submitting.';
    return;
  }

  castVote.mutate(
    {
      id: props.poll.id,
      data: {
        answers: props.poll.questions.map(question => ({
          question_id: question.id,
          option_ids: selections.value[question.id],
        })),
        unit_id: props.poll.unit_id,
      },
    },
    {
      onError: error => {
        errorMessage.value =
          (error as { response?: { data?: { message?: string } } })?.response
            ?.data?.message ??
          'We could not record your vote. Please try again.';
      },
    }
  );
}
</script>
