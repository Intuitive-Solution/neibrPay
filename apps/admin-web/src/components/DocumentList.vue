<template>
  <div class="space-y-4">
    <!-- Documents List -->
    <div v-if="documents.length > 0" class="space-y-3">
      <div
        v-for="document in documents"
        :key="document.id"
        class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200"
      >
        <div class="flex items-center space-x-4">
          <!-- File Icon -->
          <div class="flex-shrink-0">
            <div
              :class="[
                'w-10 h-10 rounded-lg flex items-center justify-center',
                getFileIconClass(document.mime_type),
              ]"
            >
              <svg
                v-if="isImageFile(document.mime_type)"
                class="w-6 h-6 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                  clip-rule="evenodd"
                />
              </svg>
              <svg
                v-else-if="isPdfFile(document.mime_type)"
                class="w-6 h-6 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                  clip-rule="evenodd"
                />
              </svg>
              <svg
                v-else
                class="w-6 h-6 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>

          <!-- Document Info -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ document.file_name }}
            </p>
            <div class="flex items-center space-x-4 text-xs text-gray-500">
              <span>{{ formatFileSize(document.file_size) }}</span>
              <span>{{ formatDate(document.created_at) }}</span>
              <span v-if="document.uploader">
                by {{ document.uploader.name }}
              </span>
            </div>
            <p
              v-if="document.description"
              class="text-xs text-gray-600 mt-1 truncate"
            >
              {{ document.description }}
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-2">
          <button
            @click="downloadDocument(document)"
            class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
            title="Download"
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
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
          </button>
          <button
            @click="deleteDocument(document)"
            :disabled="isDeleting"
            class="p-2 text-gray-400 hover:text-red-600 transition-colors duration-200 disabled:opacity-50"
            title="Delete"
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
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300"
    >
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by uploading a document.
      </p>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div
        class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/3 shadow-lg rounded-md bg-white"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Delete Document</h3>
          <button
            @click="showDeleteModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg
              class="h-6 w-6"
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

        <div class="mb-6">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <svg
                class="h-8 w-8 text-red-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
            </div>
            <div class="ml-3">
              <h4 class="text-sm font-medium text-gray-900">
                Are you sure you want to delete this document?
              </h4>
            </div>
          </div>

          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600 mb-2">
              <strong>File:</strong> {{ documentToDelete?.file_name }}
            </p>
            <p class="text-sm text-gray-500">
              This action cannot be undone. The document will be permanently
              removed.
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            Cancel
          </button>
          <button
            @click="confirmDelete"
            :disabled="isDeleting"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
          >
            <span v-if="isDeleting" class="flex items-center">
              <svg
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
              Deleting...
            </span>
            <span v-else>Delete</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { UnitDocument } from '@neibrpay/models';

interface Props {
  documents: UnitDocument[];
  isDeleting: boolean;
}

interface Emits {
  (e: 'download', document: UnitDocument): void;
  (e: 'delete', document: UnitDocument): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

const showDeleteModal = ref(false);
const documentToDelete = ref<UnitDocument | null>(null);

const downloadDocument = (document: UnitDocument) => {
  emit('download', document);
};

const deleteDocument = (document: UnitDocument) => {
  documentToDelete.value = document;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (documentToDelete.value) {
    emit('delete', documentToDelete.value);
    showDeleteModal.value = false;
    documentToDelete.value = null;
  }
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString();
};

const isImageFile = (mimeType: string): boolean => {
  return mimeType.startsWith('image/');
};

const isPdfFile = (mimeType: string): boolean => {
  return mimeType === 'application/pdf';
};

const getFileIconClass = (mimeType: string): string => {
  if (isImageFile(mimeType)) {
    return 'bg-green-500';
  } else if (isPdfFile(mimeType)) {
    return 'bg-red-500';
  } else if (mimeType.includes('word') || mimeType.includes('document')) {
    return 'bg-blue-500';
  } else if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
    return 'bg-green-600';
  } else if (mimeType.includes('zip') || mimeType.includes('archive')) {
    return 'bg-purple-500';
  } else {
    return 'bg-gray-500';
  }
};
</script>
