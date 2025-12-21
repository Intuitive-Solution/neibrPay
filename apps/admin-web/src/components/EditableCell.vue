<template>
  <div class="relative">
    <span
      v-if="!isEditing"
      @click="startEditing"
      :class="[
        'text-sm text-gray-900 cursor-pointer hover:text-primary hover:underline transition-colors',
        isTotal ? 'font-medium' : '',
      ]"
    >
      {{ formatCurrency(value) }}
    </span>
    <div v-else class="flex items-center gap-1">
      <div class="relative">
        <span
          class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-gray-500 text-sm"
        >
          $
        </span>
        <input
          ref="inputRef"
          v-model.number="localValue"
          type="number"
          step="0.01"
          min="0"
          :class="[
            'pl-6 pr-2 py-1 text-sm border border-primary rounded focus:outline-none focus:ring-2 focus:ring-primary',
            isTotal ? 'w-24 font-medium' : 'w-20',
          ]"
          @blur="handleBlur"
          @keyup.enter="handleBlur"
          @keyup.esc="cancelEditing"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';

interface Props {
  value: number;
  categoryId: number;
  month: number;
  year: number;
  isTotal?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'update', categoryId: number, month: number, value: number): void;
}>();

const isEditing = ref(false);
const localValue = ref(props.value);
const inputRef = ref<HTMLInputElement>();

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const startEditing = () => {
  isEditing.value = true;
  localValue.value = props.value;
  nextTick(() => {
    inputRef.value?.focus();
    inputRef.value?.select();
  });
};

const handleBlur = () => {
  if (localValue.value !== props.value && localValue.value >= 0) {
    emit('update', props.categoryId, props.month, localValue.value);
  }
  isEditing.value = false;
};

const cancelEditing = () => {
  localValue.value = props.value;
  isEditing.value = false;
};

watch(
  () => props.value,
  newValue => {
    if (!isEditing.value) {
      localValue.value = newValue;
    }
  }
);
</script>
