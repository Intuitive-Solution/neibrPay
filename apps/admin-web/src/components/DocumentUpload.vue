<template>
  <div class="space-y-4">
    <!-- Upload Area -->
    <div
      @drop="handleDrop"
      @dragover.prevent
      @dragenter.prevent
      @dragleave="isDragOver = false"
      @dragenter="isDragOver = true"
      :class="[
        'border-2 border-dashed rounded-lg p-6 text-center transition-colors duration-200',
        isDragOver
          ? 'border-primary bg-primary-50'
          : 'border-gray-300 hover:border-gray-400',
        isUploading ? 'opacity-50 pointer-events-none' : 'cursor-pointer',
      ]"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.csv,.zip"
        @change="handleFileSelect"
        class="hidden"
      />

      <div v-if="!isUploading">
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          stroke="currentColor"
          fill="none"
          viewBox="0 0 48 48"
        >
          <path
            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <div class="mt-4">
          <p class="text-sm text-gray-600">
            <span class="font-medium text-primary">Click to upload</span>
            or drag and drop
          </p>
          <p class="text-xs text-gray-500 mt-1">
            PDF, DOC, DOCX, TXT, JPG, PNG, GIF, XLS, XLSX, CSV, ZIP (Max 10MB
            each)
          </p>
        </div>
      </div>

      <div v-else class="flex items-center justify-center">
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
        <span class="ml-2 text-sm text-gray-600">Uploading...</span>
      </div>
    </div>

    <!-- Upload Progress -->
    <div v-if="uploadProgress > 0 && uploadProgress < 100" class="w-full">
      <div class="flex justify-between text-sm text-gray-600 mb-1">
        <span>Uploading...</span>
        <span>{{ Math.round(uploadProgress) }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-primary h-2 rounded-full transition-all duration-300"
          :style="{ width: uploadProgress + '%' }"
        ></div>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="uploadError"
      class="p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ uploadError }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  isUploading: boolean;
  uploadProgress: number;
  uploadError: string | null;
}

interface Emits {
  (e: 'upload', files: File[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const fileInput = ref<HTMLInputElement>();
const isDragOver = ref(false);

const triggerFileInput = () => {
  if (!props.isUploading) {
    fileInput.value?.click();
  }
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    const files = Array.from(target.files);
    emit('upload', files);
  }
};

const handleDrop = (event: DragEvent) => {
  isDragOver.value = false;
  if (props.isUploading) return;

  event.preventDefault();
  const files = Array.from(event.dataTransfer?.files || []);
  if (files.length > 0) {
    emit('upload', files);
  }
};
</script>
