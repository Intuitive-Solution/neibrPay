<template>
  <div class="w-full space-y-4">
    <!-- Preset Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2">
      <button
        v-for="preset in presets"
        :key="preset.value"
        @click="selectPreset(preset.value)"
        :class="[
          'px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200',
          activePreset === preset.value
            ? 'bg-primary text-white shadow-md'
            : 'bg-neutral-200 text-neutral-700 hover:bg-neutral-300',
        ]"
      >
        {{ preset.label }}
      </button>
    </div>

    <!-- Custom Date Range Inputs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="flex flex-col">
        <label
          for="start-date"
          class="text-sm font-medium text-neutral-700 mb-2"
        >
          Start Date
        </label>
        <input
          id="start-date"
          v-model="localStartDate"
          type="date"
          class="input-field"
          :disabled="isPresetSelected"
          @change="onStartDateChange"
        />
      </div>

      <div class="flex flex-col">
        <label for="end-date" class="text-sm font-medium text-neutral-700 mb-2">
          End Date
        </label>
        <input
          id="end-date"
          v-model="localEndDate"
          type="date"
          class="input-field"
          :disabled="isPresetSelected"
          @change="onEndDateChange"
        />
      </div>

      <!-- Optional: Clear Custom Range Button -->
      <div class="flex items-end">
        <button
          v-if="isCustomRange && !isPresetSelected"
          @click="clearCustomRange"
          class="w-full px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg hover:bg-neutral-200 transition-colors font-medium text-sm"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Selected Range Display -->
    <div
      v-if="localStartDate && localEndDate"
      class="p-3 bg-blue-50 border border-blue-200 rounded-lg"
    >
      <p class="text-sm text-blue-900">
        <span class="font-semibold">Selected Range:</span>
        {{ formatDate(localStartDate) }} to {{ formatDate(localEndDate) }}
        <span class="text-blue-700 ml-2">({{ daysDifference }} days)</span>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';

interface DateRangePreset {
  label: string;
  value: string;
  getDates: () => { start: Date; end: Date };
}

interface Props {
  modelValue: {
    startDate: string;
    endDate: string;
  };
}

interface Emits {
  (e: 'update:modelValue', value: Props['modelValue']): void;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => ({
    startDate: '',
    endDate: '',
  }),
});

const emit = defineEmits<Emits>();

// Local state
const localStartDate = ref(props.modelValue.startDate);
const localEndDate = ref(props.modelValue.endDate);
const activePreset = ref<string | null>(null);

// Helper function to get date N days ago
const getDaysAgo = (days: number): Date => {
  const date = new Date();
  date.setDate(date.getDate() - days);
  return date;
};

// Helper function to format date to YYYY-MM-DD
const formatDateToInput = (date: Date): string => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Helper function to format date for display
const formatDate = (dateString: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Define presets
const presets: DateRangePreset[] = [
  {
    label: 'Last 7 days',
    value: 'last7days',
    getDates: () => ({
      start: getDaysAgo(7),
      end: new Date(),
    }),
  },
  {
    label: 'Last 30 days',
    value: 'last30days',
    getDates: () => ({
      start: getDaysAgo(30),
      end: new Date(),
    }),
  },
  {
    label: 'Last 90 days',
    value: 'last90days',
    getDates: () => ({
      start: getDaysAgo(90),
      end: new Date(),
    }),
  },
  {
    label: 'Last 365 days',
    value: 'last365days',
    getDates: () => ({
      start: getDaysAgo(365),
      end: new Date(),
    }),
  },
  {
    label: 'Custom range',
    value: 'custom',
    getDates: () => ({
      start: new Date(localStartDate.value),
      end: new Date(localEndDate.value),
    }),
  },
];

// Computed properties
const isPresetSelected = computed(
  () => activePreset.value && activePreset.value !== 'custom'
);

const isCustomRange = computed(() => activePreset.value === 'custom');

const daysDifference = computed(() => {
  if (!localStartDate.value || !localEndDate.value) return 0;
  const start = new Date(localStartDate.value);
  const end = new Date(localEndDate.value);
  return (
    Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  );
});

// Methods
const selectPreset = (presetValue: string) => {
  if (presetValue === 'custom') {
    activePreset.value = 'custom';
    return;
  }

  const preset = presets.find(p => p.value === presetValue);
  if (preset) {
    const { start, end } = preset.getDates();
    localStartDate.value = formatDateToInput(start);
    localEndDate.value = formatDateToInput(end);
    activePreset.value = presetValue;
    emitUpdate();
  }
};

const onStartDateChange = () => {
  if (localStartDate.value && localEndDate.value) {
    activePreset.value = 'custom';
    emitUpdate();
  }
};

const onEndDateChange = () => {
  if (localStartDate.value && localEndDate.value) {
    activePreset.value = 'custom';
    emitUpdate();
  }
};

const clearCustomRange = () => {
  localStartDate.value = '';
  localEndDate.value = '';
  activePreset.value = null;
  emitUpdate();
};

const emitUpdate = () => {
  emit('update:modelValue', {
    startDate: localStartDate.value,
    endDate: localEndDate.value,
  });
};

// Initialize with "Last 90 days" as default
onMounted(() => {
  if (!props.modelValue.startDate && !props.modelValue.endDate) {
    selectPreset('last90days');
  }
});

// Watch for external changes
watch(
  () => props.modelValue,
  newValue => {
    if (newValue.startDate) localStartDate.value = newValue.startDate;
    if (newValue.endDate) localEndDate.value = newValue.endDate;
  },
  { deep: true }
);
</script>

<style scoped>
.input-field {
  @apply w-full px-3 py-2 border border-neutral-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all;
}

.input-field:disabled {
  @apply bg-neutral-100 text-neutral-500 cursor-not-allowed;
}
</style>
