<template>
  <div :id="`poll-${poll.id}`" class="scroll-mt-24">
    <!-- Needs vote: expanded ballot -->
    <div
      v-if="variant === 'needs-vote' && expanded"
      class="bg-white rounded-xl border border-primary/30 shadow-sm overflow-hidden"
    >
      <div
        class="flex items-center justify-between gap-4 px-5 py-3.5 bg-primary-50 border-b border-primary/15"
      >
        <div class="flex items-center gap-2.5 min-w-0 text-primary-800">
          <svg
            class="w-5 h-5 flex-shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <span class="text-sm font-medium truncate">{{ closingLabel }}</span>
        </div>
        <span
          class="badge badge-paid flex-shrink-0 inline-flex items-center gap-1.5"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
          Open
        </span>
      </div>

      <div class="p-5 sm:p-6 space-y-5">
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

        <p v-if="errorMessage" class="text-sm text-red-600">
          {{ errorMessage }}
        </p>

        <div class="flex flex-wrap items-center gap-4 pt-1">
          <button
            type="button"
            class="btn-primary"
            :disabled="!allAnswered || castVote.isPending.value"
            @click="submitVote"
          >
            {{ castVote.isPending.value ? 'Submitting...' : 'Submit vote' }}
          </button>
          <span class="text-[13px] text-gray-500 max-w-md">
            Anonymous — the board sees that your unit voted, not what you
            picked.
          </span>
        </div>
      </div>
    </div>

    <!-- Needs vote: collapsed row -->
    <div
      v-else-if="variant === 'needs-vote'"
      class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4"
    >
      <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-[15px] font-semibold text-gray-900 truncate">
              {{ poll.title }}
            </h3>
            <span
              v-if="closesInBadge"
              class="badge bg-amber-100 text-amber-800 whitespace-nowrap"
            >
              {{ closesInBadge }}
            </span>
          </div>
          <p class="mt-1 text-[13px] text-gray-500">{{ ballotMeta }}</p>
        </div>
        <button
          type="button"
          class="btn-primary btn-sm flex-shrink-0 self-start sm:self-center"
          @click="$emit('expand')"
        >
          Vote now
        </button>
      </div>
    </div>

    <!-- Voted · awaiting results -->
    <div
      v-else-if="variant === 'voted'"
      class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4"
    >
      <div class="flex items-center gap-4">
        <div
          class="w-10 h-10 rounded-full bg-primary-100 text-primary flex items-center justify-center flex-shrink-0"
        >
          <svg
            class="w-5 h-5"
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
        <div class="min-w-0 flex-1">
          <h3 class="text-[15px] font-semibold text-gray-900 truncate">
            {{ poll.title }}
          </h3>
          <p class="mt-0.5 text-[13px] text-gray-500">
            {{ votedAwaitingLabel }}
          </p>
        </div>
        <span class="badge bg-primary-100 text-primary-800 flex-shrink-0">
          You voted
        </span>
      </div>
    </div>

    <!-- Closed poll -->
    <div
      v-else
      class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
    >
      <button
        type="button"
        class="w-full flex items-start justify-between gap-4 px-5 py-4 text-left hover:bg-gray-50 transition-colors"
        @click="$emit('expand')"
      >
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-[15px] font-semibold text-gray-900">
              {{ poll.title }}
            </h3>
            <span class="badge bg-gray-100 text-gray-600">Closed</span>
          </div>
          <p class="mt-1 text-[13px] text-gray-500">{{ closedSummary }}</p>
        </div>
        <svg
          :class="[
            'w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5 transition-transform',
            expanded ? 'rotate-180' : '',
          ]"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </button>

      <div v-if="expanded" class="px-5 pb-5 border-t border-gray-100 pt-4">
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useCastVote } from '../composables/usePolls';
import PollResultBars from './PollResultBars.vue';
import {
  allowsMultipleAnswers,
  formatQuestionSummary,
  getQuestionTypeLabel,
  type PollQuestion,
  type ResidentPoll,
} from '@neibrpay/models';

const props = withDefaults(
  defineProps<{
    poll: ResidentPoll;
    variant?: 'needs-vote' | 'voted' | 'closed';
    expanded?: boolean;
  }>(),
  {
    variant: 'needs-vote',
    expanded: false,
  }
);

defineEmits<{
  expand: [];
}>();

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

const closesInBadge = computed(() => {
  if (!props.poll.closes_at) {
    return null;
  }

  const days = Math.ceil(
    (new Date(props.poll.closes_at).getTime() - Date.now()) / 86_400_000
  );

  if (days <= 0) {
    return 'Closes today';
  }

  return `Closes in ${days} day${days === 1 ? '' : 's'}`;
});

const ballotMeta = computed(() => {
  const questions = props.poll.questions;
  const parts: string[] = [];

  if (questions.length === 1) {
    parts.push(getQuestionTypeLabel(questions[0].type));
  } else if (questions.length > 1) {
    const firstType = questions[0]?.type;
    const allSame = questions.every(question => question.type === firstType);
    if (allSame && firstType) {
      parts.push(getQuestionTypeLabel(firstType));
    }
    parts.push(`${questions.length} questions`);
  } else {
    parts.push(formatQuestionSummary(questions));
  }

  const minutes = Math.max(1, Math.ceil(questions.length * 0.5));
  parts.push(`about ${minutes} minute${minutes === 1 ? '' : 's'}`);

  return parts.join(' · ');
});

const votedAwaitingLabel = computed(() => {
  const parts: string[] = [];

  if (props.poll.voted_at) {
    parts.push(`Your unit voted ${formatDate(props.poll.voted_at)}`);
  } else {
    parts.push('Your unit voted');
  }

  if (props.poll.closes_at) {
    parts.push(
      `results shared when it closes ${formatDate(props.poll.closes_at)}`
    );
  } else {
    parts.push('results shared when it closes');
  }

  return parts.join(' · ');
});

const closedSummary = computed(() => {
  const parts: string[] = [];

  if (props.poll.closes_at) {
    parts.push(`Closed ${formatDate(props.poll.closes_at)}`);
  }

  parts.push(
    `${props.poll.responded_unit_count} of ${props.poll.target_unit_count} units voted`
  );

  if (props.poll.questions.length > 0) {
    parts.push(
      `${props.poll.questions.length} question${
        props.poll.questions.length === 1 ? '' : 's'
      }`
    );
  }

  return parts.join(' · ');
});

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
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
