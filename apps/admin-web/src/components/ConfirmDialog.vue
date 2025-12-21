<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <!-- Overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          @click="handleCancel"
        ></div>

        <!-- Modal Container -->
        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <!-- Icon -->
                <div
                  :class="[
                    'mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10',
                    iconBgClass,
                  ]"
                >
                  <svg
                    v-if="type === 'danger'"
                    class="h-6 w-6 text-red-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                    />
                  </svg>
                  <svg
                    v-else-if="type === 'warning'"
                    class="h-6 w-6 text-yellow-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                    />
                  </svg>
                  <svg
                    v-else
                    class="h-6 w-6 text-primary"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
                    />
                  </svg>
                </div>

                <!-- Content -->
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <h3
                    class="text-lg font-semibold leading-6 text-gray-900"
                    id="modal-title"
                  >
                    {{ title }}
                  </h3>
                  <div class="mt-2">
                    <p v-if="!$slots.default" class="text-sm text-gray-500">
                      {{ message }}
                    </p>
                    <slot></slot>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
            >
              <button
                type="button"
                :class="[
                  'inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto',
                  confirmButtonClass,
                  isLoading
                    ? 'opacity-50 cursor-not-allowed'
                    : 'hover:opacity-90',
                ]"
                @click="handleConfirm"
                :disabled="isLoading"
              >
                {{ isLoading ? loadingText : confirmText }}
              </button>
              <button
                type="button"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                @click="handleCancel"
                :disabled="isLoading"
              >
                {{ cancelText }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  isOpen: boolean;
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  loadingText?: string;
  type?: 'danger' | 'warning' | 'info';
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  loadingText: 'Processing...',
  type: 'danger',
  isLoading: false,
});

const emit = defineEmits<{
  confirm: [];
  cancel: [];
}>();

const iconBgClass = computed(() => {
  switch (props.type) {
    case 'danger':
      return 'bg-red-100';
    case 'warning':
      return 'bg-yellow-100';
    default:
      return 'bg-primary-100';
  }
});

const confirmButtonClass = computed(() => {
  switch (props.type) {
    case 'danger':
      return 'bg-red-600 hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600';
    case 'warning':
      return 'bg-yellow-600 hover:bg-yellow-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600';
    default:
      return 'bg-primary hover:bg-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary';
  }
});

const handleConfirm = () => {
  if (!props.isLoading) {
    emit('confirm');
  }
};

const handleCancel = () => {
  if (!props.isLoading) {
    emit('cancel');
  }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}
</style>
