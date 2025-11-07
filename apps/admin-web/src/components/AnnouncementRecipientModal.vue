<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <div
      class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
    >
      <!-- Background overlay -->
      <div
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        aria-hidden="true"
        @click="handleClose"
      ></div>

      <!-- Modal panel -->
      <div
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
      >
        <!-- Modal header -->
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modal-title">
              Select Recipients
            </h3>
            <button
              @click="handleClose"
              class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
            >
              <svg
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
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

          <!-- Selected recipients display -->
          <div
            v-if="selectedRecipients.length > 0"
            class="mb-4 flex flex-wrap gap-2"
          >
            <span
              v-for="(recipient, index) in selectedRecipients"
              :key="index"
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
            >
              {{ getRecipientLabel(recipient) }}
              <button
                @click="removeRecipient(index)"
                class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary-200"
              >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
            </span>
          </div>

          <!-- Tabs -->
          <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  activeTab === tab.id
                    ? 'border-primary text-primary'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                ]"
              >
                {{ tab.name }}
              </button>
            </nav>
          </div>

          <!-- All Members / All Admins Tab -->
          <div v-if="activeTab === 'groups'" class="space-y-4">
            <div
              class="flex items-center p-4 hover:bg-gray-50 cursor-pointer rounded-lg border border-gray-200"
              @click="toggleGroup('all_members')"
            >
              <input
                :checked="isSelected({ recipient_type: 'all_members' })"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                @click.stop="toggleGroup('all_members')"
              />
              <div class="ml-3">
                <div class="text-sm font-medium text-gray-900">All Members</div>
                <div class="text-sm text-gray-500">
                  All residents in the community
                </div>
              </div>
            </div>
            <div
              class="flex items-center p-4 hover:bg-gray-50 cursor-pointer rounded-lg border border-gray-200"
              @click="toggleGroup('all_admins')"
            >
              <input
                :checked="isSelected({ recipient_type: 'all_admins' })"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                @click.stop="toggleGroup('all_admins')"
              />
              <div class="ml-3">
                <div class="text-sm font-medium text-gray-900">All Admins</div>
                <div class="text-sm text-gray-500">All administrators</div>
              </div>
            </div>
          </div>

          <!-- Units Tab -->
          <div v-if="activeTab === 'units'">
            <!-- Filter dropdown -->
            <div class="mb-4">
              <select
                v-model="unitFilter"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
              >
                <option value="all">All properties</option>
                <option value="associations">All associations</option>
                <option value="rentals">All rentals</option>
              </select>
            </div>

            <!-- Search bar -->
            <div class="relative mb-4">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </div>
              <input
                v-model="unitSearchQuery"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm"
                placeholder="Search by property name..."
              />
            </div>

            <!-- Units list -->
            <div
              class="max-h-64 overflow-y-auto border border-gray-200 rounded-md"
            >
              <div
                v-if="isLoadingUnits"
                class="flex items-center justify-center py-8"
              >
                <svg
                  class="animate-spin h-8 w-8 text-primary"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <span class="ml-2 text-gray-600">Loading units...</span>
              </div>

              <div
                v-else-if="filteredUnits.length === 0"
                class="p-4 text-center text-gray-500"
              >
                <p v-if="unitSearchQuery">
                  No units found matching your search.
                </p>
                <p v-else>No units available.</p>
              </div>

              <div v-else class="divide-y divide-gray-200">
                <div
                  v-for="unit in filteredUnits"
                  :key="unit.id"
                  class="p-4 hover:bg-gray-50 cursor-pointer"
                  @click="toggleUnit(unit.id)"
                >
                  <div class="flex items-center">
                    <input
                      :checked="
                        isSelected({
                          recipient_type: 'unit',
                          recipient_id: unit.id,
                        })
                      "
                      type="checkbox"
                      class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                      @click.stop="toggleUnit(unit.id)"
                    />
                    <div class="ml-3 flex-1">
                      <div class="text-sm font-medium text-gray-900">
                        {{ unit.title }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ unit.address }}, {{ unit.city }}, {{ unit.state }}
                        {{ unit.zip_code }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Residents Tab -->
          <div v-if="activeTab === 'residents'">
            <!-- Search bar -->
            <div class="relative mb-4">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </div>
              <input
                v-model="residentSearchQuery"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm"
                placeholder="Search by resident name or email..."
              />
            </div>

            <!-- Residents list -->
            <div
              class="max-h-64 overflow-y-auto border border-gray-200 rounded-md"
            >
              <div
                v-if="isLoadingResidents"
                class="flex items-center justify-center py-8"
              >
                <svg
                  class="animate-spin h-8 w-8 text-primary"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <span class="ml-2 text-gray-600">Loading residents...</span>
              </div>

              <div
                v-else-if="filteredResidents.length === 0"
                class="p-4 text-center text-gray-500"
              >
                <p v-if="residentSearchQuery">
                  No residents found matching your search.
                </p>
                <p v-else>No residents available.</p>
              </div>

              <div v-else class="divide-y divide-gray-200">
                <div
                  v-for="resident in filteredResidents"
                  :key="resident.id"
                  class="p-4 hover:bg-gray-50 cursor-pointer"
                  @click="toggleResident(resident.id)"
                >
                  <div class="flex items-center">
                    <input
                      :checked="
                        isSelected({
                          recipient_type: 'resident',
                          recipient_id: resident.id,
                        })
                      "
                      type="checkbox"
                      class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                      @click.stop="toggleResident(resident.id)"
                    />
                    <div class="ml-3 flex-1">
                      <div class="text-sm font-medium text-gray-900">
                        {{ resident.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ resident.email }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            @click="handleDone"
            :disabled="selectedRecipients.length === 0"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Done
          </button>
          <button
            @click="handleClose"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useResidents } from '../composables/useResidents';
import { unitsApi } from '@neibrpay/api-client';
import type { Unit, Resident } from '@neibrpay/models';
import type { RecipientType } from '@neibrpay/models';

interface Recipient {
  recipient_type: RecipientType;
  recipient_id?: number | null;
}

interface Props {
  isOpen: boolean;
  modelValue: Recipient[];
}

interface Emits {
  (e: 'update:modelValue', value: Recipient[]): void;
  (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const tabs = [
  { id: 'groups', name: 'Groups' },
  { id: 'units', name: 'Units' },
  { id: 'residents', name: 'Residents' },
];

const activeTab = ref('groups');
const selectedRecipients = ref<Recipient[]>([...props.modelValue]);

// Units
const unitFilter = ref('all');
const unitSearchQuery = ref('');
const units = ref<Unit[]>([]);
const isLoadingUnits = ref(false);

// Residents
const residentSearchQuery = ref('');
const { data: residents, isLoading: isLoadingResidents } = useResidents(false);

// Load units on mount and when tab changes
watch([() => props.isOpen, activeTab], async ([isOpen, tab]) => {
  if (isOpen && tab === 'units' && units.value.length === 0) {
    await loadUnits();
  }
});

async function loadUnits() {
  isLoadingUnits.value = true;
  try {
    units.value = await unitsApi.getUnits(false);
  } catch (error) {
    console.error('Failed to load units:', error);
  } finally {
    isLoadingUnits.value = false;
  }
}

const filteredUnits = computed(() => {
  let result = units.value;

  if (unitSearchQuery.value) {
    const query = unitSearchQuery.value.toLowerCase();
    result = result.filter(
      unit =>
        unit.title.toLowerCase().includes(query) ||
        unit.address.toLowerCase().includes(query) ||
        unit.city.toLowerCase().includes(query)
    );
  }

  return result;
});

const filteredResidents = computed(() => {
  if (!residents.value) return [];

  let result = residents.value;

  if (residentSearchQuery.value) {
    const query = residentSearchQuery.value.toLowerCase();
    result = result.filter(
      resident =>
        resident.name.toLowerCase().includes(query) ||
        resident.email.toLowerCase().includes(query)
    );
  }

  return result;
});

function isSelected(recipient: Recipient): boolean {
  return selectedRecipients.value.some(
    r =>
      r.recipient_type === recipient.recipient_type &&
      r.recipient_id === recipient.recipient_id
  );
}

function toggleGroup(type: 'all_members' | 'all_admins') {
  const recipient: Recipient = { recipient_type: type };
  const index = selectedRecipients.value.findIndex(
    r => r.recipient_type === type
  );

  if (index > -1) {
    selectedRecipients.value.splice(index, 1);
  } else {
    selectedRecipients.value.push(recipient);
  }
}

function toggleUnit(unitId: number) {
  const recipient: Recipient = {
    recipient_type: 'unit',
    recipient_id: unitId,
  };
  const index = selectedRecipients.value.findIndex(
    r => r.recipient_type === 'unit' && r.recipient_id === unitId
  );

  if (index > -1) {
    selectedRecipients.value.splice(index, 1);
  } else {
    selectedRecipients.value.push(recipient);
  }
}

function toggleResident(residentId: number) {
  const recipient: Recipient = {
    recipient_type: 'resident',
    recipient_id: residentId,
  };
  const index = selectedRecipients.value.findIndex(
    r => r.recipient_type === 'resident' && r.recipient_id === residentId
  );

  if (index > -1) {
    selectedRecipients.value.splice(index, 1);
  } else {
    selectedRecipients.value.push(recipient);
  }
}

function removeRecipient(index: number) {
  selectedRecipients.value.splice(index, 1);
}

function getRecipientLabel(recipient: Recipient): string {
  if (recipient.recipient_type === 'all_members') {
    return 'All Members';
  }
  if (recipient.recipient_type === 'all_admins') {
    return 'All Admins';
  }
  if (recipient.recipient_type === 'unit') {
    const unit = units.value.find(u => u.id === recipient.recipient_id);
    return unit ? unit.title : `Unit #${recipient.recipient_id}`;
  }
  if (recipient.recipient_type === 'resident') {
    const resident = residents.value?.find(
      r => r.id === recipient.recipient_id
    );
    return resident ? resident.name : `Resident #${recipient.recipient_id}`;
  }
  return 'Unknown';
}

function handleDone() {
  emit('update:modelValue', [...selectedRecipients.value]);
  emit('close');
}

function handleClose() {
  selectedRecipients.value = [...props.modelValue];
  emit('close');
}

// Sync with prop changes
watch(
  () => props.modelValue,
  newValue => {
    selectedRecipients.value = [...newValue];
  },
  { deep: true }
);
</script>
