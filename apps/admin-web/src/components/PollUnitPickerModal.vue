<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
    @click.self="handleClose"
  >
    <div
      class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[85vh] flex flex-col"
    >
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Select units</h2>
        <p class="text-sm text-gray-500 mt-1">
          Each selected unit gets one vote
        </p>
      </div>

      <div class="px-6 py-3 border-b border-gray-100">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by unit or street"
          class="input-field"
        />
      </div>

      <div class="flex-1 overflow-y-auto px-6 py-3">
        <p v-if="isLoading" class="text-sm text-gray-500 py-6 text-center">
          Loading units...
        </p>
        <p
          v-else-if="filteredUnits.length === 0"
          class="text-sm text-gray-500 py-6 text-center"
        >
          No units match that search.
        </p>
        <label
          v-for="unit in filteredUnits"
          :key="unit.id"
          class="flex items-center gap-3 py-2.5 border-b border-gray-100 last:border-0 cursor-pointer"
        >
          <input
            type="checkbox"
            :checked="selectedUnitIds.includes(unit.id)"
            @change="toggleUnit(unit.id)"
            class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
          />
          <span class="flex-1">
            <span class="block text-sm font-medium text-gray-900">{{
              unit.title
            }}</span>
            <span class="block text-[13px] text-gray-500">{{
              unit.address
            }}</span>
          </span>
        </label>
      </div>

      <div
        class="px-6 py-4 border-t border-gray-200 flex items-center justify-between gap-3"
      >
        <span class="text-sm text-gray-500">
          {{ selectedUnitIds.length }} unit{{
            selectedUnitIds.length === 1 ? '' : 's'
          }}
          selected
        </span>
        <div class="flex gap-3">
          <button type="button" class="btn-outline btn-sm" @click="handleClose">
            Cancel
          </button>
          <button type="button" class="btn-primary btn-sm" @click="handleDone">
            Done
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { unitsApi } from '@neibrpay/api-client';
import type { Unit } from '@neibrpay/models';

interface Props {
  isOpen: boolean;
  modelValue: number[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'update:modelValue', value: number[]): void;
  (e: 'close'): void;
}>();

const searchQuery = ref('');
const units = ref<Unit[]>([]);
const isLoading = ref(false);
const selectedUnitIds = ref<number[]>([...props.modelValue]);

watch(
  () => props.isOpen,
  async isOpen => {
    if (!isOpen) return;

    selectedUnitIds.value = [...props.modelValue];

    if (units.value.length === 0) {
      isLoading.value = true;
      try {
        units.value = await unitsApi.getUnits(false);
      } catch (error) {
        console.error('Failed to load units:', error);
      } finally {
        isLoading.value = false;
      }
    }
  },
  { immediate: true }
);

const filteredUnits = computed(() => {
  if (!searchQuery.value) {
    return units.value;
  }

  const query = searchQuery.value.toLowerCase();
  return units.value.filter(
    unit =>
      unit.title.toLowerCase().includes(query) ||
      unit.address.toLowerCase().includes(query) ||
      unit.city.toLowerCase().includes(query)
  );
});

function toggleUnit(unitId: number) {
  const index = selectedUnitIds.value.indexOf(unitId);

  if (index > -1) {
    selectedUnitIds.value.splice(index, 1);
  } else {
    selectedUnitIds.value.push(unitId);
  }
}

function handleDone() {
  emit('update:modelValue', [...selectedUnitIds.value]);
  emit('close');
}

function handleClose() {
  selectedUnitIds.value = [...props.modelValue];
  emit('close');
}
</script>
