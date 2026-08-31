<template>
  <div
    v-if="!results || results.questions.length === 0"
    class="text-sm text-gray-500"
  >
    No votes were cast on this poll.
  </div>

  <div v-else class="space-y-6">
    <div
      v-for="(question, questionIndex) in results.questions"
      :key="question.question_id"
      class="space-y-3"
    >
      <div
        v-if="results.questions.length > 1 || showPrompt"
        class="flex items-baseline justify-between gap-3"
      >
        <h4 class="text-[15px] font-semibold text-gray-900">
          <span v-if="results.questions.length > 1" class="text-gray-400"
            >{{ questionIndex + 1 }}.
          </span>
          {{ question.prompt }}
        </h4>
        <span class="text-[13px] text-gray-500 whitespace-nowrap">
          {{ question.units_answered }}
          {{ question.units_answered === 1 ? 'unit' : 'units' }} answered
        </span>
      </div>

      <p v-if="question.total_votes === 0" class="text-sm text-gray-500">
        No votes yet.
      </p>

      <div v-else class="space-y-3.5">
        <div
          v-for="(option, index) in question.options"
          :key="option.id"
          class="space-y-1.5"
        >
          <div class="flex items-baseline justify-between gap-3">
            <span class="flex items-center gap-2">
              <span
                :class="[
                  'text-[15px] text-gray-900',
                  index === leadingIndexFor(question)
                    ? 'font-semibold'
                    : 'font-medium',
                ]"
              >
                {{ option.label }}
              </span>
              <span
                v-if="showWinner && index === leadingIndexFor(question)"
                class="badge bg-primary-100 text-primary-800"
              >
                Winner
              </span>
            </span>
            <span
              :class="[
                'text-sm font-semibold whitespace-nowrap',
                index === leadingIndexFor(question)
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
                index === leadingIndexFor(question)
                  ? 'bg-primary'
                  : 'bg-gray-300',
              ]"
              :style="{ width: option.percentage + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PollQuestionResults, PollResults } from '@neibrpay/models';

withDefaults(
  defineProps<{
    results: PollResults | null;
    showWinner?: boolean;
    /** Show the prompt even when the poll has a single question */
    showPrompt?: boolean;
  }>(),
  { showWinner: true, showPrompt: false }
);

/**
 * Index of the option with the most votes; -1 when there is a tie for first,
 * so a tied poll never crowns an arbitrary winner.
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
</script>
