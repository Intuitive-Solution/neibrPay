<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="card-modern">
        <h3 class="text-sm font-medium text-gray-600">Open polls</h3>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ openCount }}</p>
      </div>
      <div class="card-modern">
        <h3 class="text-sm font-medium text-gray-600">Units voting now</h3>
        <p class="text-2xl font-bold text-gray-900 mt-1">
          {{ unitsVotingLabel }}
        </p>
      </div>
      <div class="card-modern">
        <h3 class="text-sm font-medium text-gray-600">Avg. participation</h3>
        <p class="text-2xl font-bold text-gray-900 mt-1">
          {{ averageParticipation }}%
        </p>
      </div>
    </div>

    <!-- Header + Actions -->
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <!-- Status Tabs -->
      <div class="inline-flex gap-1.5 p-1 bg-gray-100 rounded-lg w-fit">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="statusFilter = tab.value"
          :class="[
            'px-3.5 py-1.5 rounded-md text-sm transition-colors duration-150',
            statusFilter === tab.value
              ? 'bg-white shadow-sm font-semibold text-gray-900'
              : 'font-medium text-gray-600 hover:text-gray-900',
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="hidden md:flex items-center gap-3">
        <router-link to="/polls/create">
          <button class="btn-primary">
            <svg
              class="w-5 h-5 mr-2"
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
            New Poll
          </button>
        </router-link>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="card-modern text-center py-12">
      <p class="text-gray-500">Loading polls...</p>
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <p class="text-sm text-red-800">
        We couldn't load your polls. Please try again.
      </p>
    </div>

    <!-- Empty State -->
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
        {{ statusFilter === 'all' ? 'No polls yet' : 'Nothing here yet' }}
      </h3>
      <p class="mt-3 max-w-md text-[15px] leading-relaxed text-gray-600">
        Create your first poll to gauge where the community stands. Each of your
        {{ unitCount }} units gets one vote, and you'll see who has voted
        without seeing what they chose.
      </p>
      <div class="mt-5 flex flex-wrap justify-center gap-3">
        <router-link to="/polls/create">
          <button class="btn-primary">Create your first poll</button>
        </router-link>
      </div>
    </div>

    <!-- Poll Table -->
    <div
      v-else
      class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
    >
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
              >
                Poll
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell w-64"
              >
                Units voted
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell"
              >
                Closes
              </th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="poll in polls" :key="poll.id" class="table-row-hover">
              <td class="px-6 py-4 cursor-pointer" @click="openPoll(poll.id)">
                <div class="text-[15px] font-semibold text-gray-900">
                  {{ poll.title }}
                </div>
                <div class="text-[13px] text-gray-500 mt-0.5">
                  {{ formatQuestionSummary(poll.questions) }} ·
                  {{
                    poll.status === 'draft'
                      ? 'not published'
                      : formatAudienceSummary(poll)
                  }}
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['badge', statusBadgeClass(poll.status)]">
                  <span
                    v-if="poll.status === 'open'"
                    class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5"
                  ></span>
                  {{ getPollStatusLabel(poll.status) }}
                </span>
              </td>

              <td class="px-6 py-4 hidden md:table-cell">
                <div
                  v-if="poll.status === 'draft'"
                  class="text-sm text-gray-400"
                >
                  —
                </div>
                <div v-else class="flex items-center gap-3">
                  <div
                    class="flex-1 h-2 rounded-full bg-gray-200 overflow-hidden"
                  >
                    <div
                      :class="[
                        'h-full rounded-full',
                        poll.status === 'closed' ? 'bg-gray-400' : 'bg-primary',
                      ]"
                      :style="{ width: participationPercentage(poll) + '%' }"
                    ></div>
                  </div>
                  <span
                    class="text-[13px] font-semibold text-gray-900 whitespace-nowrap"
                  >
                    {{ poll.responded_unit_count }} of
                    {{ poll.target_unit_count }}
                  </span>
                </div>
              </td>

              <td
                class="px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell"
                :class="poll.closes_at ? 'text-gray-600' : 'text-gray-400'"
              >
                {{ closesLabel(poll) }}
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end">
                  <DropdownMenu
                    trigger-class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 shadow-sm hover:shadow-md transition-all duration-200"
                  >
                    <template #default="{ close }">
                      <button
                        @click="
                          () => {
                            openPoll(poll.id);
                            close();
                          }
                        "
                        class="dropdown-item"
                      >
                        View results
                      </button>
                      <button
                        v-if="poll.status !== 'closed'"
                        @click="
                          () => {
                            editPoll(poll.id);
                            close();
                          }
                        "
                        class="dropdown-item"
                      >
                        Edit
                      </button>
                      <button
                        v-if="poll.status === 'draft'"
                        @click="
                          () => {
                            publish(poll);
                            close();
                          }
                        "
                        class="dropdown-item"
                      >
                        Publish
                      </button>
                      <button
                        v-if="poll.status === 'open'"
                        @click="
                          () => {
                            closeNow(poll);
                            close();
                          }
                        "
                        class="dropdown-item"
                      >
                        Close now
                      </button>
                      <div class="border-t border-gray-200 my-1"></div>
                      <button
                        @click="
                          () => {
                            askDelete(poll);
                            close();
                          }
                        "
                        class="dropdown-item dropdown-item-danger"
                      >
                        Delete
                      </button>
                    </template>
                  </DropdownMenu>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <ConfirmDialog
      :is-open="!!pollToDelete"
      title="Delete Poll"
      :message="`Delete &quot;${pollToDelete?.title}&quot;? Votes already cast will be removed with it.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletePoll.isPending.value"
      @confirm="confirmDelete"
      @cancel="pollToDelete = null"
    />

    <!-- Mobile Fixed Bottom Button -->
    <div
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/polls/create" class="block">
        <button class="btn-primary w-full">New Poll</button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  usePolls,
  useClosePoll,
  useDeletePoll,
  usePublishPoll,
} from '../composables/usePolls';
import {
  formatAudienceSummary,
  formatQuestionSummary,
  getPollStatusLabel,
  participationPercentage,
  PollStatus,
  type Poll,
  type PollFilters,
} from '@neibrpay/models';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import DropdownMenu from '../components/DropdownMenu.vue';

const router = useRouter();
const authStore = useAuthStore();

// Check admin access
if (!authStore.isAdmin) {
  router.push('/');
}

const statusTabs = [
  { value: 'all' as const, label: 'All' },
  { value: PollStatus.OPEN, label: 'Open' },
  { value: PollStatus.DRAFT, label: 'Drafts' },
  { value: PollStatus.CLOSED, label: 'Closed' },
];

const statusFilter = ref<PollStatus | 'all'>('all');
const pollToDelete = ref<Poll | null>(null);

const filters = computed<PollFilters>(() => ({
  status: statusFilter.value,
}));

const { data, isLoading, error } = usePolls(filters);
const publishPoll = usePublishPoll();
const closePoll = useClosePoll();
const deletePoll = useDeletePoll();

const polls = computed(() => data.value?.data ?? []);
const openCount = computed(() => data.value?.meta.open_count ?? 0);
const unitCount = computed(() => data.value?.meta.unit_count ?? 0);

const openPolls = computed(() =>
  polls.value.filter(s => s.status === PollStatus.OPEN)
);

const unitsVotingLabel = computed(() => {
  if (openPolls.value.length === 0) {
    return '—';
  }

  const voted = openPolls.value.reduce(
    (sum, s) => sum + s.responded_unit_count,
    0
  );
  const target = openPolls.value.reduce(
    (sum, s) => sum + s.target_unit_count,
    0
  );

  return `${voted} of ${target}`;
});

const averageParticipation = computed(() => {
  const scored = polls.value.filter(
    s => s.status !== PollStatus.DRAFT && s.target_unit_count > 0
  );

  if (scored.length === 0) {
    return 0;
  }

  const total = scored.reduce((sum, s) => sum + participationPercentage(s), 0);
  return Math.round(total / scored.length);
});

function statusBadgeClass(status: PollStatus): string {
  switch (status) {
    case PollStatus.OPEN:
      return 'badge-paid';
    case PollStatus.DRAFT:
      return 'badge-draft';
    default:
      return 'bg-gray-100 text-gray-600';
  }
}

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function closesLabel(poll: Poll): string {
  if (poll.status === PollStatus.DRAFT) {
    return '—';
  }

  if (poll.status === PollStatus.CLOSED) {
    return poll.closes_at ? `Closed ${formatDate(poll.closes_at)}` : 'Closed';
  }

  return poll.closes_at ? formatDate(poll.closes_at) : 'No close date';
}

function openPoll(id: number) {
  router.push(`/polls/${id}`);
}

function editPoll(id: number) {
  router.push(`/polls/${id}/edit`);
}

function publish(poll: Poll) {
  publishPoll.mutate(poll.id);
}

function closeNow(poll: Poll) {
  closePoll.mutate(poll.id);
}

function askDelete(poll: Poll) {
  pollToDelete.value = poll;
}

function confirmDelete() {
  const poll = pollToDelete.value;
  if (!poll) return;

  deletePoll.mutate(poll.id, {
    onSettled: () => {
      pollToDelete.value = null;
    },
  });
}
</script>
