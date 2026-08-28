<template>
  <div v-if="isEditMode && isLoadingPoll" class="max-w-3xl">
    <div class="card-modern text-center py-12">
      <p class="text-gray-500">Loading poll...</p>
    </div>
  </div>

  <div v-else-if="isEditMode && (pollError || !existingPoll)" class="max-w-3xl">
    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-sm text-red-800">We couldn't load this poll.</p>
    </div>
  </div>

  <div v-else class="max-w-3xl space-y-5 pb-24 md:pb-0">
    <!-- Header -->
    <div class="card-modern">
      <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div>
          <h1 class="text-xl font-semibold text-gray-900">
            {{ isEditMode ? 'Edit poll' : 'New poll' }}
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            {{
              form.questions.length === 1
                ? 'One question, one vote per unit'
                : `${form.questions.length} questions, one vote per unit`
            }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button
            v-if="!hasVotes"
            type="button"
            class="btn-outline btn-sm"
            :disabled="isSubmitting"
            @click="submit(PollStatus.DRAFT)"
          >
            Save draft
          </button>
          <button
            type="button"
            class="btn-primary btn-sm"
            :disabled="isSubmitting"
            @click="submit(PollStatus.OPEN)"
          >
            {{ isSubmitting ? 'Saving...' : 'Publish' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Locked notice -->
    <div
      v-if="isLocked"
      class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-900"
    >
      Votes have already been cast, so the questions, options, and audience are
      locked. You can still change the title, description, close date, and
      results visibility.
    </div>

    <!-- General error -->
    <div
      v-if="errors.general"
      class="p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <p class="text-sm text-red-800">{{ errors.general }}</p>
    </div>

    <div
      v-if="successMessage"
      class="p-4 bg-green-50 border border-green-200 rounded-lg"
    >
      <p class="text-sm text-green-800">{{ successMessage }}</p>
    </div>

    <!-- Title & description -->
    <div class="card-modern space-y-4">
      <div>
        <label
          for="title"
          class="block text-sm font-medium text-gray-700 mb-1.5"
        >
          Title <span class="text-red-500">*</span>
        </label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          class="input-field"
          :class="{ 'border-red-300': errors.title }"
          placeholder="Summer pool hours"
        />
        <p v-if="errors.title" class="mt-1.5 text-sm text-red-600">
          {{ errors.title }}
        </p>
      </div>

      <div>
        <label
          for="description"
          class="block text-sm font-medium text-gray-700 mb-1.5"
        >
          Description <span class="text-gray-400 font-normal">(optional)</span>
        </label>
        <textarea
          id="description"
          v-model="form.description"
          rows="3"
          class="input-field"
          placeholder="Give residents the context behind the questions."
        ></textarea>
      </div>
    </div>

    <!-- Questions -->
    <div
      v-for="(question, questionIndex) in form.questions"
      :key="question.key"
      class="card-modern space-y-4"
    >
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-base font-semibold text-gray-900">
          {{
            form.questions.length > 1
              ? `Question ${questionIndex + 1}`
              : 'Question'
          }}
        </h2>
        <button
          v-if="form.questions.length > 1 && !isLocked"
          type="button"
          class="text-sm font-medium text-gray-500 hover:text-red-600 transition-colors"
          @click="removeQuestion(questionIndex)"
        >
          Remove question
        </button>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
          Question type
        </label>
        <div class="inline-flex flex-wrap gap-1.5 p-1 bg-gray-100 rounded-lg">
          <button
            v-for="type in questionTypes"
            :key="type.value"
            type="button"
            :disabled="isLocked"
            @click="setQuestionType(question, type.value)"
            :class="[
              'px-4 py-1.5 rounded-md text-sm transition-colors duration-150',
              question.type === type.value
                ? 'bg-white shadow-sm font-semibold text-gray-900'
                : 'font-medium text-gray-600 hover:text-gray-900',
              isLocked ? 'opacity-50 cursor-not-allowed' : '',
            ]"
          >
            {{ type.label }}
          </button>
        </div>
        <p
          v-if="question.type === PollQuestionType.MULTI_SELECT"
          class="mt-1.5 text-[13px] text-gray-500"
        >
          Residents can pick more than one option.
        </p>
      </div>

      <div>
        <label
          :for="`prompt-${question.key}`"
          class="block text-sm font-medium text-gray-700 mb-1.5"
        >
          Prompt <span class="text-red-500">*</span>
        </label>
        <input
          :id="`prompt-${question.key}`"
          v-model="question.prompt"
          type="text"
          :disabled="isLocked"
          class="input-field"
          :class="{ 'border-red-300': questionErrors[questionIndex]?.prompt }"
          placeholder="Which pool schedule do you prefer for the rest of the summer?"
        />
        <p
          v-if="questionErrors[questionIndex]?.prompt"
          class="mt-1.5 text-sm text-red-600"
        >
          {{ questionErrors[questionIndex].prompt }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Options</label
        >
        <div class="space-y-2">
          <div
            v-for="(option, optionIndex) in question.options"
            :key="optionIndex"
            class="flex items-center gap-2.5"
          >
            <input
              v-model="option.label"
              type="text"
              :disabled="isLocked || isYesNo(question)"
              class="input-field flex-1"
              :placeholder="`Option ${optionIndex + 1}`"
            />
            <button
              v-if="
                !isYesNo(question) && !isLocked && question.options.length > 2
              "
              type="button"
              class="p-2 text-gray-400 hover:text-red-600 transition-colors"
              :aria-label="`Remove option ${optionIndex + 1}`"
              @click="removeOption(question, optionIndex)"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
        <button
          v-if="!isYesNo(question) && !isLocked"
          type="button"
          class="mt-2 text-sm font-medium text-primary hover:text-primary-600"
          @click="addOption(question)"
        >
          + Add option
        </button>
        <p
          v-if="questionErrors[questionIndex]?.options"
          class="mt-1.5 text-sm text-red-600"
        >
          {{ questionErrors[questionIndex].options }}
        </p>
      </div>
    </div>

    <div v-if="!isLocked">
      <button type="button" class="btn-outline" @click="addQuestion">
        <svg
          class="w-4 h-4 mr-2"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
          />
        </svg>
        Add another question
      </button>
      <p v-if="errors.questions" class="mt-1.5 text-sm text-red-600">
        {{ errors.questions }}
      </p>
    </div>

    <!-- Who votes -->
    <div class="card-modern space-y-3">
      <div>
        <h2 class="text-base font-semibold text-gray-900">Who votes</h2>
        <p class="text-sm text-gray-500 mt-1">
          Polls target units — one vote per unit, first owner to submit records
          it
        </p>
      </div>

      <button
        type="button"
        :disabled="isLocked"
        @click="audience = 'all_units'"
        :class="[
          'w-full flex items-center gap-3 px-3.5 py-3 rounded-lg text-left transition-colors',
          audience === 'all_units'
            ? 'border-2 border-primary bg-primary-50'
            : 'border border-gray-200 bg-white hover:bg-gray-50',
          isLocked ? 'opacity-60 cursor-not-allowed' : '',
        ]"
      >
        <span
          :class="[
            'w-[18px] h-[18px] rounded-full box-border flex-shrink-0',
            audience === 'all_units'
              ? 'border-[5px] border-primary bg-white'
              : 'border border-gray-300',
          ]"
        ></span>
        <span>
          <span class="block text-sm font-semibold text-gray-900"
            >All units</span
          >
          <span class="block text-[13px] text-gray-500">
            Everyone in the community votes
          </span>
        </span>
      </button>

      <button
        type="button"
        :disabled="isLocked"
        @click="audience = 'specific'"
        :class="[
          'w-full flex items-center gap-3 px-3.5 py-3 rounded-lg text-left transition-colors',
          audience === 'specific'
            ? 'border-2 border-primary bg-primary-50'
            : 'border border-gray-200 bg-white hover:bg-gray-50',
          isLocked ? 'opacity-60 cursor-not-allowed' : '',
        ]"
      >
        <span
          :class="[
            'w-[18px] h-[18px] rounded-full box-border flex-shrink-0',
            audience === 'specific'
              ? 'border-[5px] border-primary bg-white'
              : 'border border-gray-300',
          ]"
        ></span>
        <span>
          <span class="block text-sm font-semibold text-gray-900">
            Specific units
          </span>
          <span class="block text-[13px] text-gray-500">
            {{
              selectedUnitIds.length > 0
                ? `${selectedUnitIds.length} unit${selectedUnitIds.length === 1 ? '' : 's'} selected`
                : 'Pick units by street or number'
            }}
          </span>
        </span>
      </button>

      <button
        v-if="audience === 'specific' && !isLocked"
        type="button"
        class="btn-outline btn-sm"
        @click="showUnitPicker = true"
      >
        Choose units
      </button>

      <p v-if="errors.recipients" class="text-sm text-red-600">
        {{ errors.recipients }}
      </p>
    </div>

    <!-- Timing & results -->
    <div class="card-modern space-y-4">
      <h2 class="text-base font-semibold text-gray-900">
        Timing &amp; results
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label
            for="opens"
            class="block text-sm font-medium text-gray-700 mb-1.5"
          >
            Opens
          </label>
          <input
            id="opens"
            v-model="form.opens_at"
            type="date"
            class="input-field"
          />
        </div>
        <div>
          <label
            for="closes"
            class="block text-sm font-medium text-gray-700 mb-1.5"
          >
            Closes
          </label>
          <input
            id="closes"
            v-model="form.closes_at"
            type="date"
            class="input-field"
          />
          <p v-if="errors.closes_at" class="mt-1.5 text-sm text-red-600">
            {{ errors.closes_at }}
          </p>
        </div>
      </div>

      <div>
        <label
          for="visibility"
          class="block text-sm font-medium text-gray-700 mb-1.5"
        >
          Results visible to residents
        </label>
        <select
          id="visibility"
          v-model="form.results_visibility"
          class="input-field"
        >
          <option value="after_close">After the poll closes</option>
          <option value="live">Live, as votes come in</option>
          <option value="admins_only">Board only</option>
        </select>
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
          Answers are anonymous. You'll see which units have voted so you can
          send reminders, but never how a unit answered.
        </p>
      </div>
    </div>

    <!-- Footer actions -->
    <div class="flex items-center justify-end gap-3">
      <button type="button" class="btn-outline" @click="handleCancel">
        Cancel
      </button>
      <button
        type="button"
        class="btn-primary"
        :disabled="isSubmitting"
        @click="submit(isEditMode ? currentStatus : PollStatus.OPEN)"
      >
        {{
          isSubmitting
            ? 'Saving...'
            : isEditMode
              ? 'Save changes'
              : 'Publish poll'
        }}
      </button>
    </div>

    <PollUnitPickerModal
      :is-open="showUnitPicker"
      :model-value="selectedUnitIds"
      @update:model-value="selectedUnitIds = $event"
      @close="showUnitPicker = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { usePoll, useCreatePoll, useUpdatePoll } from '../composables/usePolls';
import PollUnitPickerModal from '../components/PollUnitPickerModal.vue';
import {
  PollQuestionType,
  PollRecipientType,
  PollResultsVisibility,
  PollStatus,
  type CreatePollDto,
  type Poll,
} from '@neibrpay/models';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

if (!authStore.isAdmin) {
  router.push('/');
}

const pollId = computed(() => {
  const id = route.params.id;
  return id ? parseInt(id as string) : null;
});

const isEditMode = computed(() => !!pollId.value);

const questionTypes = [
  { value: PollQuestionType.SINGLE_CHOICE, label: 'Single choice' },
  { value: PollQuestionType.MULTI_SELECT, label: 'Multiple choice' },
  { value: PollQuestionType.YES_NO, label: 'Yes / No' },
];

interface QuestionForm {
  /** Stable key so v-for doesn't reuse inputs across reorders */
  key: number;
  prompt: string;
  type: PollQuestionType;
  options: Array<{ label: string }>;
}

let nextQuestionKey = 0;

function blankQuestion(): QuestionForm {
  return {
    key: nextQuestionKey++,
    prompt: '',
    type: PollQuestionType.SINGLE_CHOICE,
    options: [{ label: '' }, { label: '' }],
  };
}

const form = ref({
  title: '',
  description: '' as string,
  opens_at: '' as string,
  closes_at: '' as string,
  results_visibility: PollResultsVisibility.AFTER_CLOSE,
  questions: [blankQuestion()] as QuestionForm[],
});

const audience = ref<'all_units' | 'specific'>('all_units');
const selectedUnitIds = ref<number[]>([]);
const showUnitPicker = ref(false);
const currentStatus = ref<PollStatus.DRAFT | PollStatus.OPEN>(PollStatus.DRAFT);
const hasVotes = ref(false);

const errors = ref({
  general: '',
  title: '',
  questions: '',
  recipients: '',
  closes_at: '',
});

const successMessage = ref('');

// Parallel to form.questions - index matches
const questionErrors = ref<Array<{ prompt: string; options: string }>>([]);

const isLocked = computed(() => hasVotes.value);

const {
  data: existingPoll,
  isLoading: isLoadingPoll,
  error: pollError,
} = usePoll(pollId);
const createPoll = useCreatePoll();
const updatePoll = useUpdatePoll();

const isSubmitting = computed(
  () => createPoll.isPending.value || updatePoll.isPending.value
);

// Hydrate as soon as the poll is available — including when Vue Query
// already has it cached from the results page (watch without immediate
// would never run in that case).
watch(
  existingPoll,
  (poll: Poll | undefined) => {
    if (!poll || !isEditMode.value) return;

    const questions = poll.questions ?? [];
    const recipients = poll.recipients ?? [];

    form.value = {
      title: poll.title,
      description: poll.description ?? '',
      opens_at: toDateInput(poll.opens_at),
      closes_at: toDateInput(poll.closes_at),
      results_visibility: poll.results_visibility,
      questions:
        questions.length > 0
          ? questions.map(question => ({
              key: nextQuestionKey++,
              prompt: question.prompt,
              type: question.type,
              options: (question.options ?? []).map(o => ({ label: o.label })),
            }))
          : [blankQuestion()],
    };

    const targetsAll = recipients.some(
      r => r.recipient_type === PollRecipientType.ALL_UNITS
    );
    audience.value = targetsAll ? 'all_units' : 'specific';
    selectedUnitIds.value = recipients
      .filter(r => r.recipient_type === PollRecipientType.UNIT)
      .map(r => r.recipient_id as number);

    currentStatus.value =
      poll.status === PollStatus.OPEN ? PollStatus.OPEN : PollStatus.DRAFT;
    hasVotes.value = (poll.responded_unit_count ?? 0) > 0;
  },
  { immediate: true }
);

function toDateInput(value: string | null): string {
  return value ? new Date(value).toISOString().slice(0, 10) : '';
}

function isYesNo(question: QuestionForm): boolean {
  return question.type === PollQuestionType.YES_NO;
}

function setQuestionType(question: QuestionForm, type: PollQuestionType) {
  if (isLocked.value) return;

  const wasYesNo = isYesNo(question);
  question.type = type;

  if (type === PollQuestionType.YES_NO) {
    question.options = [{ label: 'Yes' }, { label: 'No' }];
  } else if (wasYesNo) {
    // Leaving Yes/No: clear the fixed labels so the admin writes their own
    question.options = [{ label: '' }, { label: '' }];
  }
}

function addQuestion() {
  form.value.questions.push(blankQuestion());
}

function removeQuestion(index: number) {
  form.value.questions.splice(index, 1);
  questionErrors.value.splice(index, 1);
}

function addOption(question: QuestionForm) {
  question.options.push({ label: '' });
}

function removeOption(question: QuestionForm, index: number) {
  question.options.splice(index, 1);
}

function validate(status: PollStatus.DRAFT | PollStatus.OPEN): boolean {
  errors.value = {
    general: '',
    title: '',
    questions: '',
    recipients: '',
    closes_at: '',
  };

  questionErrors.value = form.value.questions.map(() => ({
    prompt: '',
    options: '',
  }));

  if (!form.value.title.trim()) {
    errors.value.title = 'The title is required.';
  }

  if (
    form.value.opens_at &&
    form.value.closes_at &&
    form.value.closes_at <= form.value.opens_at
  ) {
    errors.value.closes_at = 'The close date must be after the open date.';
  }

  // Drafts can be incomplete — only the title is required to save the work
  if (status === PollStatus.DRAFT) {
    return !errors.value.title && !errors.value.closes_at;
  }

  if (form.value.questions.length === 0) {
    errors.value.questions = 'A poll needs at least one question.';
  }

  form.value.questions.forEach((question, index) => {
    if (!question.prompt.trim()) {
      questionErrors.value[index].prompt = 'The question is required.';
    }

    const labels = question.options.map(o => o.label.trim());
    if (labels.length < 2 || labels.some(label => !label)) {
      questionErrors.value[index].options =
        'Every option needs a label, and there must be at least two.';
    } else if (new Set(labels).size !== labels.length) {
      questionErrors.value[index].options = 'Options must be unique.';
    }
  });

  if (audience.value === 'specific' && selectedUnitIds.value.length === 0) {
    errors.value.recipients = 'Select at least one unit.';
  }

  const hasQuestionError = questionErrors.value.some(
    q => q.prompt || q.options
  );

  return !Object.values(errors.value).some(Boolean) && !hasQuestionError;
}

function buildPayload(
  status: PollStatus.DRAFT | PollStatus.OPEN
): CreatePollDto {
  const recipients =
    audience.value === 'all_units'
      ? [{ recipient_type: PollRecipientType.ALL_UNITS }]
      : selectedUnitIds.value.map(id => ({
          recipient_type: PollRecipientType.UNIT,
          recipient_id: id,
        }));

  return {
    title: form.value.title.trim(),
    description: form.value.description.trim() || null,
    status,
    opens_at: form.value.opens_at || null,
    closes_at: form.value.closes_at || null,
    results_visibility: form.value.results_visibility,
    questions: form.value.questions.map(question => ({
      prompt: question.prompt.trim(),
      type: question.type,
      options: question.options.map(o => ({ label: o.label.trim() })),
    })),
    recipients,
  };
}

function submit(status: PollStatus.DRAFT | PollStatus.OPEN) {
  if (!validate(status)) return;

  successMessage.value = '';
  const payload = buildPayload(status);

  const onError = (error: unknown) => {
    const apiError = error as {
      message?: string;
      errors?: Record<string, string[]>;
    };
    const firstFieldError = apiError.errors
      ? Object.values(apiError.errors).flat()[0]
      : undefined;
    errors.value.general =
      firstFieldError ||
      apiError.message ||
      'Something went wrong. Please try again.';
  };

  if (isEditMode.value && pollId.value) {
    updatePoll.mutate(
      { id: pollId.value, data: payload },
      {
        onSuccess: () => {
          if (status === PollStatus.DRAFT) {
            currentStatus.value = PollStatus.DRAFT;
            successMessage.value = 'Draft saved.';
            return;
          }
          router.push(`/polls/${pollId.value}`);
        },
        onError,
      }
    );
    return;
  }

  createPoll.mutate(payload, {
    onSuccess: result => {
      if (status === PollStatus.DRAFT) {
        router.push(`/polls/${result.data.id}/edit`);
        return;
      }
      router.push(`/polls/${result.data.id}`);
    },
    onError,
  });
}

function handleCancel() {
  router.push('/polls');
}
</script>
