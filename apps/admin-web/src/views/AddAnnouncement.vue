<template>
  <div class="max-w-4xl">
    <!-- Header Section -->
    <div class="card-modern card mb-6">
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
        <div class="mb-4 lg:mb-0">
          <div class="flex items-center gap-4 mb-2">
            <h1 class="text-2xl font-bold text-gray-900">
              {{ isEditMode ? 'Edit Announcement' : 'Add New Announcement' }}
            </h1>
          </div>
          <p class="text-gray-600">
            {{
              isEditMode
                ? 'Update announcement information and details'
                : 'Create a new announcement for your HOA community'
            }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="handleCancel" class="btn-outline">
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
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            Back to Announcements
          </button>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="errors.general"
      class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"
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
          <p class="text-sm text-red-800">{{ errors.general }}</p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="card-modern bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- To Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label class="block text-sm font-medium text-gray-700 lg:pt-3">
            To <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="showRecipientModal = true"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
              >
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
                Select Recipients
              </button>
              <div
                v-if="form.recipients.length > 0"
                class="flex flex-wrap gap-2"
              >
                <span
                  v-for="(recipient, index) in form.recipients"
                  :key="index"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
                >
                  {{ getRecipientLabel(recipient) }}
                </span>
              </div>
              <span v-else class="text-sm text-gray-500"
                >No recipients selected</span
              >
            </div>
            <p v-if="errors.recipients" class="mt-2 text-sm text-red-600">
              {{ errors.recipients }}
            </p>
          </div>
        </div>

        <!-- Subject Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="subject"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Subject <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="subject"
              v-model="form.subject"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.subject,
              }"
              placeholder="Enter announcement subject"
            />
            <p v-if="errors.subject" class="mt-2 text-sm text-red-600">
              {{ errors.subject }}
            </p>
          </div>
        </div>

        <!-- Message Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="message"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Message <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <!-- Rich Text Editor -->
            <div class="border border-gray-300 rounded-lg">
              <!-- Toolbar -->
              <div class="border-b border-gray-200 p-3 bg-gray-50">
                <div class="flex flex-wrap gap-2">
                  <!-- Formatting buttons -->
                  <div class="flex items-center space-x-1">
                    <select
                      v-model="selectedFormat"
                      @change="formatBlock(selectedFormat)"
                      class="text-sm border border-gray-300 rounded px-2 py-1"
                    >
                      <option value="p">Paragraph</option>
                      <option value="h1">Heading 1</option>
                      <option value="h2">Heading 2</option>
                      <option value="h3">Heading 3</option>
                    </select>
                    <select
                      v-model="selectedFont"
                      @change="formatText('fontName', selectedFont)"
                      class="text-sm border border-gray-300 rounded px-2 py-1"
                    >
                      <option value="Helvetica">Helvetica</option>
                      <option value="Arial">Arial</option>
                      <option value="Times New Roman">Times New Roman</option>
                      <option value="Georgia">Georgia</option>
                    </select>
                    <select
                      v-model="selectedSize"
                      @change="formatText('fontSize', selectedSize)"
                      class="text-sm border border-gray-300 rounded px-2 py-1"
                    >
                      <option value="3">12px</option>
                      <option value="4">14px</option>
                      <option value="5">16px</option>
                      <option value="6">18px</option>
                      <option value="7">24px</option>
                    </select>
                  </div>

                  <div class="flex items-center space-x-1">
                    <button
                      @click="formatText('bold')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('bold')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Bold"
                    >
                      <span class="font-bold text-sm">B</span>
                    </button>
                    <button
                      @click="formatText('italic')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('italic')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Italic"
                    >
                      <span class="italic text-sm">I</span>
                    </button>
                    <button
                      @click="formatText('underline')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('underline')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Underline"
                    >
                      <span class="underline text-sm">U</span>
                    </button>
                  </div>

                  <div class="flex items-center space-x-1">
                    <button
                      @click="formatText('justifyLeft')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('justifyLeft')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Align Left"
                    >
                      <span class="text-sm">⬅️</span>
                    </button>
                    <button
                      @click="formatText('justifyCenter')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('justifyCenter')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Align Center"
                    >
                      <span class="text-sm">↔️</span>
                    </button>
                    <button
                      @click="formatText('justifyRight')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('justifyRight')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Align Right"
                    >
                      <span class="text-sm">➡️</span>
                    </button>
                  </div>

                  <div class="flex items-center space-x-1">
                    <button
                      @click="formatText('insertUnorderedList')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('insertUnorderedList')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Bullet List"
                    >
                      <span class="text-sm">•</span>
                    </button>
                    <button
                      @click="formatText('insertOrderedList')"
                      :class="[
                        'p-1 rounded',
                        isFormatActive('insertOrderedList')
                          ? 'bg-blue-200'
                          : 'hover:bg-gray-200',
                      ]"
                      type="button"
                      title="Numbered List"
                    >
                      <span class="text-sm">1.</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Editor Content Area -->
              <div class="p-4 min-h-48 overflow-y-auto">
                <div
                  ref="editorRef"
                  contenteditable="true"
                  @input="updateContent"
                  @keyup="updateFormatState"
                  @mouseup="updateFormatState"
                  class="rich-text-editor w-full border-0 outline-none resize-none text-sm focus:outline-none"
                  :class="{
                    'border-red-300': errors.message,
                  }"
                  placeholder="Write a short message..."
                ></div>
              </div>

              <!-- Status Bar -->
              <div
                class="border-t border-gray-200 px-4 py-2 bg-gray-50 flex justify-between items-center text-xs text-gray-500"
              >
                <span>{{ getCurrentTag() }}</span>
                <span
                  >{{ getWordCount(getPlainText(form.message)) }} words</span
                >
              </div>
            </div>
            <p v-if="errors.message" class="mt-2 text-sm text-red-600">
              {{ errors.message }}
            </p>
          </div>
        </div>

        <!-- Removal Date -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label class="block text-sm font-medium text-gray-700 lg:pt-3">
            Removal Date
          </label>
          <div class="lg:col-span-2">
            <div v-if="!form.removal_date" class="flex items-center">
              <button
                type="button"
                @click="showDatePicker = true"
                class="text-primary hover:text-primary-600 text-sm font-medium"
              >
                + Set removal date
              </button>
            </div>
            <div v-else class="flex items-center gap-3">
              <input
                v-model="form.removal_date"
                type="date"
                :min="minDate"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm"
              />
              <button
                type="button"
                @click="form.removal_date = null"
                class="text-red-600 hover:text-red-700 text-sm"
              >
                Remove
              </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">
              Announcement will be automatically hidden after this date
            </p>
          </div>
        </div>

        <!-- Form Actions -->
        <div
          class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200"
        >
          <button
            type="button"
            @click="handleCancel"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isSubmitting" class="flex items-center">
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
              {{ isEditMode ? 'Updating...' : 'Creating...' }}
            </span>
            <span v-else>{{
              isEditMode ? 'Update Announcement' : 'Create Announcement'
            }}</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Recipient Selection Modal -->
    <AnnouncementRecipientModal
      :is-open="showRecipientModal"
      :model-value="form.recipients"
      @update:model-value="form.recipients = $event"
      @close="showRecipientModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  useCreateAnnouncement,
  useUpdateAnnouncement,
  useAnnouncement,
} from '../composables/useAnnouncements';
import AnnouncementRecipientModal from '../components/AnnouncementRecipientModal.vue';
import type { RecipientType } from '@neibrpay/models';

interface Recipient {
  recipient_type: RecipientType;
  recipient_id?: number | null;
}

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Check admin access
if (!authStore.isAdmin) {
  router.push('/');
}

const announcementId = computed(() => {
  const id = route.params.id;
  return id ? parseInt(id as string) : null;
});

const isEditMode = computed(() => !!announcementId.value);

// Form data
const form = ref({
  subject: '',
  message: '',
  removal_date: null as string | null,
  recipients: [] as Recipient[],
});

// Form errors
const errors = ref({
  general: '',
  subject: '',
  message: '',
  recipients: '',
});

// Loading states
const isSubmitting = ref(false);
const showRecipientModal = ref(false);
const showDatePicker = ref(false);

// Rich text editor
const editorRef = ref<HTMLElement | null>(null);
const selectedFormat = ref('p');
const selectedFont = ref('Helvetica');
const selectedSize = ref('4');
const formatState = ref({
  bold: false,
  italic: false,
  underline: false,
  strikeThrough: false,
  justifyLeft: false,
  justifyCenter: false,
  justifyRight: false,
  justifyFull: false,
  insertUnorderedList: false,
  insertOrderedList: false,
});

// Get announcement if editing
const { data: announcement, isLoading: isLoadingAnnouncement } =
  useAnnouncement(announcementId);

// Watch for announcement data
watch(
  announcement,
  newAnnouncement => {
    if (newAnnouncement && isEditMode.value) {
      form.value.subject = newAnnouncement.subject;
      form.value.message = newAnnouncement.message;
      form.value.removal_date = newAnnouncement.removal_date || null;
      form.value.recipients = (newAnnouncement.recipients || []).map(r => ({
        recipient_type: r.recipient_type,
        recipient_id: r.recipient_id,
      }));

      // Set editor content
      nextTick(() => {
        if (editorRef.value) {
          editorRef.value.innerHTML = newAnnouncement.message;
        }
      });
    }
  },
  { immediate: true }
);

const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

function getRecipientLabel(recipient: Recipient): string {
  if (recipient.recipient_type === 'all_members') return 'All Members';
  if (recipient.recipient_type === 'all_admins') return 'All Admins';
  if (recipient.recipient_type === 'unit')
    return `Unit #${recipient.recipient_id}`;
  if (recipient.recipient_type === 'resident')
    return `Resident #${recipient.recipient_id}`;
  return 'Unknown';
}

// Rich text editor methods
const formatText = (command: string, value?: string) => {
  if (editorRef.value) {
    editorRef.value.focus();
    document.execCommand(command, false, value);
    updateFormatState();
  }
};

const formatBlock = (tag: string) => {
  if (editorRef.value) {
    editorRef.value.focus();
    document.execCommand('formatBlock', false, tag);
    updateFormatState();
  }
};

const isFormatActive = (command: string) => {
  return formatState.value[command as keyof typeof formatState.value] || false;
};

const updateFormatState = () => {
  if (!editorRef.value) return;
  formatState.value.bold = document.queryCommandState('bold');
  formatState.value.italic = document.queryCommandState('italic');
  formatState.value.underline = document.queryCommandState('underline');
  formatState.value.strikeThrough = document.queryCommandState('strikeThrough');
  formatState.value.justifyLeft = document.queryCommandState('justifyLeft');
  formatState.value.justifyCenter = document.queryCommandState('justifyCenter');
  formatState.value.justifyRight = document.queryCommandState('justifyRight');
  formatState.value.justifyFull = document.queryCommandState('justifyFull');
  formatState.value.insertUnorderedList = document.queryCommandState(
    'insertUnorderedList'
  );
  formatState.value.insertOrderedList =
    document.queryCommandState('insertOrderedList');

  const selection = window.getSelection();
  if (selection && selection.rangeCount > 0) {
    const range = selection.getRangeAt(0);
    const container = range.commonAncestorContainer;
    const element =
      container.nodeType === Node.TEXT_NODE
        ? container.parentElement
        : (container as Element);
    if (element) {
      const tagName = element.tagName.toLowerCase();
      if (['h1', 'h2', 'h3', 'p'].includes(tagName)) {
        selectedFormat.value = tagName;
      } else {
        selectedFormat.value = 'p';
      }
    }
  }
};

const updateContent = () => {
  if (editorRef.value) {
    form.value.message = editorRef.value.innerHTML;
  }
};

const getCurrentTag = () => {
  if (!editorRef.value) return 'p';
  const selection = window.getSelection();
  if (selection && selection.rangeCount > 0) {
    const range = selection.getRangeAt(0);
    const container = range.commonAncestorContainer;
    const element =
      container.nodeType === Node.TEXT_NODE
        ? container.parentElement
        : (container as Element);
    return element?.tagName?.toLowerCase() || 'p';
  }
  return 'p';
};

const getPlainText = (html: string) => {
  const temp = document.createElement('div');
  temp.innerHTML = html;
  return temp.textContent || temp.innerText || '';
};

const getWordCount = (text: string) => {
  if (!text) return 0;
  return text
    .trim()
    .split(/\s+/)
    .filter((word: string) => word.length > 0).length;
};

// Mutations
const createMutation = useCreateAnnouncement();
const updateMutation = useUpdateAnnouncement();

const handleSubmit = async () => {
  // Clear errors
  errors.value = {
    general: '',
    subject: '',
    message: '',
    recipients: '',
  };

  // Validation
  if (!form.value.subject.trim()) {
    errors.value.subject = 'Subject is required';
    return;
  }

  if (!form.value.message.trim() || form.value.message === '<br>') {
    errors.value.message = 'Message is required';
    return;
  }

  if (form.value.recipients.length === 0) {
    errors.value.recipients = 'At least one recipient must be selected';
    return;
  }

  isSubmitting.value = true;

  try {
    const data = {
      subject: form.value.subject.trim(),
      message: form.value.message,
      removal_date: form.value.removal_date || null,
      recipients: form.value.recipients,
    };

    if (isEditMode.value) {
      await updateMutation.mutateAsync({
        id: announcementId.value!,
        data,
      });
    } else {
      await createMutation.mutateAsync(data);
    }

    router.push('/announcements');
  } catch (error: any) {
    console.error('Error saving announcement:', error);
    errors.value.general =
      error.response?.data?.message ||
      'Failed to save announcement. Please try again.';
  } finally {
    isSubmitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/announcements');
};

onMounted(() => {
  if (!isEditMode.value && editorRef.value) {
    editorRef.value.innerHTML = '';
  }
});
</script>

<style scoped>
.rich-text-editor h1 {
  font-size: 2rem !important;
  font-weight: bold !important;
  margin: 0.5rem 0 !important;
  line-height: 1.2 !important;
  color: #1f2937 !important;
}

.rich-text-editor h2 {
  font-size: 1.5rem !important;
  font-weight: bold !important;
  margin: 0.5rem 0 !important;
  line-height: 1.3 !important;
  color: #1f2937 !important;
}

.rich-text-editor h3 {
  font-size: 1.25rem !important;
  font-weight: bold !important;
  margin: 0.5rem 0 !important;
  line-height: 1.4 !important;
  color: #1f2937 !important;
}

.rich-text-editor p {
  margin: 0.5rem 0 !important;
  line-height: 1.5 !important;
}

.rich-text-editor ul,
.rich-text-editor ol {
  margin: 0.5rem 0 !important;
  padding-left: 1.5rem !important;
}

.rich-text-editor li {
  margin: 0.25rem 0 !important;
}
</style>
